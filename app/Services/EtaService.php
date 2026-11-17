<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class EtaService
{
    /**
     * Calculate delay duration and SLA severity based on expected vs estimated arrival.
     *
     * @return array{
     *     is_delayed: bool,
     *     delay_hours: int,
     *     delay_minutes: int,
     *     delay_severity: string,
     *     reason: string
     * }
     */
    public function calculateDelay(CarbonInterface $expectedArrival, ?CarbonInterface $estimatedArrival): array
    {
        if ($estimatedArrival === null || $estimatedArrival->lessThanOrEqualTo($expectedArrival)) {
            return [
                'is_delayed' => false,
                'delay_hours' => 0,
                'delay_minutes' => 0,
                'delay_severity' => 'NONE',
                'reason' => 'On Schedule / Ahead of Schedule',
            ];
        }

        // Calculate positive duration difference
        $diffInMinutes = (int) abs($expectedArrival->diffInMinutes($estimatedArrival, false));
        $delayHours = (int) ceil($diffInMinutes / 60);

        $config = config('cargotrace.delay_thresholds');
        $lowMax = $config['low']['max_hours'] ?? 6;
        $mediumMax = $config['medium']['max_hours'] ?? 24;

        if ($delayHours <= $lowMax) {
            $severity = 'LOW';
        } elseif ($delayHours <= $mediumMax) {
            $severity = 'MEDIUM';
        } else {
            $severity = 'HIGH';
        }

        return [
            'is_delayed' => true,
            'delay_hours' => $delayHours,
            'delay_minutes' => $diffInMinutes,
            'delay_severity' => $severity,
            'reason' => "Estimated arrival exceeds expected SLA arrival by {$delayHours} hours.",
        ];
    }

    /**
     * Update shipment estimated arrival time and recalculate delay metrics atomically.
     */
    public function updateShipmentEta(Shipment $shipment, CarbonInterface $newEstimatedArrival, ?string $reason = null): Shipment
    {
        $delay = $this->calculateDelay($shipment->expected_arrival, $newEstimatedArrival);

        return DB::transaction(function () use ($shipment, $newEstimatedArrival, $delay) {
            $shipment->update([
                'estimated_arrival' => $newEstimatedArrival,
                'is_delayed' => $delay['is_delayed'],
                'delay_hours' => $delay['delay_hours'],
                'delay_severity' => $delay['delay_severity'],
            ]);

            return $shipment->refresh();
        });
    }

    /**
     * Recalculate and update delay attributes based on existing estimated arrival in the database.
     */
    public function recalculateShipmentDelay(Shipment $shipment): Shipment
    {
        $delay = $this->calculateDelay($shipment->expected_arrival, $shipment->estimated_arrival);

        return DB::transaction(function () use ($shipment, $delay) {
            $shipment->update([
                'is_delayed' => $delay['is_delayed'],
                'delay_hours' => $delay['delay_hours'],
                'delay_severity' => $delay['delay_severity'],
            ]);

            return $shipment->refresh();
        });
    }
}
