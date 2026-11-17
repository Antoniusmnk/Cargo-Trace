<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExceptionService
{
    public function __construct(
        protected EtaService $etaService
    ) {
    }

    /**
     * Create a new shipment exception disruption and optionally recalculate ETA impact.
     *
     * @param  array<string, mixed>  $data
     */
    public function createException(Shipment $shipment, array $data, User $reporter): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $reporter) {
            $data['reported_by'] = $reporter->id;
            $data['detected_at'] = $data['detected_at'] ?? now();
            $data['status'] = $data['status'] ?? 'OPEN';

            $exception = $shipment->exceptions()->create($data);

            if (isset($data['impact_hours']) && (int) $data['impact_hours'] > 0) {
                $baseEta = $shipment->estimated_arrival ?? $shipment->expected_arrival;
                $newEta = $baseEta->copy()->addHours((int) $data['impact_hours']);

                $this->etaService->updateShipmentEta(
                    $shipment,
                    $newEta,
                    "Adjusted due to {$data['exception_type']}: {$data['description']}"
                );
            }

            return $exception->load(['shipment', 'reporter']);
        });
    }

    /**
     * Update the investigation or resolution status of a shipment exception.
     *
     * @throws DomainException
     */
    public function updateStatus(ShipmentException $exception, string $newStatus, ?string $resolutionNotes = null): ShipmentException
    {
        if ($exception->status === 'RESOLVED') {
            throw new DomainException("Gangguan kargo dengan status RESOLVED bersifat final dan tidak dapat dibuka kembali.");
        }

        $validTransitions = [
            'OPEN' => ['INVESTIGATING', 'RESOLVED'],
            'INVESTIGATING' => ['RESOLVED'],
        ];

        if (! in_array($newStatus, $validTransitions[$exception->status] ?? [], true)) {
            throw new DomainException("Transisi status exception dari {$exception->status} ke {$newStatus} tidak valid.");
        }

        return DB::transaction(function () use ($exception, $newStatus, $resolutionNotes) {
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'RESOLVED') {
                $updateData['resolved_at'] = now();
                $updateData['resolution_notes'] = $resolutionNotes ?? 'Disruption resolved.';
            }

            $exception->update($updateData);

            return $exception->refresh();
        });
    }

    /**
     * Retrieve all disruption exceptions for a shipment ordered chronologically.
     *
     * @return Collection<int, ShipmentException>
     */
    public function getExceptionsForShipment(Shipment $shipment): Collection
    {
        return $shipment->exceptions()
            ->with('reporter')
            ->orderBy('detected_at', 'desc')
            ->get();
    }
}
