<?php

namespace App\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    /**
     * Linear strict state machine transition map.
     */
    public const ALLOWED_TRANSITIONS = [
        'CREATED'    => ['BOOKED'],
        'BOOKED'     => ['PICKED_UP'],
        'PICKED_UP'  => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED'  => [], // Terminal status, tidak boleh ada transisi lagi!
    ];

    /**
     * Check if status transition is valid according to state machine rules.
     */
    public function isValidTransition(string $currentStatus, string $newStatus): bool
    {
        // Terminal status: no transitions allowed
        if ($currentStatus === 'DELIVERED') {
            return false;
        }

        // Allow repeated checkpoint updates during IN_TRANSIT
        if ($currentStatus === 'IN_TRANSIT' && $newStatus === 'IN_TRANSIT') {
            return true;
        }

        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$currentStatus] ?? [], true);
    }

    /**
     * Add a verified tracking event to shipment timeline and update shipment status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidStatusTransitionException
     */
    public function addTrackingEvent(Shipment $shipment, array $data, User $actor): TrackingEvent
    {
        $newStatus = $data['status'];

        if ($shipment->current_status === 'DELIVERED') {
            throw new InvalidStatusTransitionException("Kargo dengan status DELIVERED bersifat terminal dan tidak dapat menerima pembaruan status lagi.");
        }

        if ($newStatus !== $shipment->current_status) {
            if (! $this->isValidTransition($shipment->current_status, $newStatus)) {
                throw new InvalidStatusTransitionException(
                    "Transisi status dari {$shipment->current_status} ke {$newStatus} tidak valid. Urutan alur wajib: CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED."
                );
            }
        } else {
            // Same status: allowed for IN_TRANSIT checkpoints or initial CREATED event
            $isInitialCreated = ($newStatus === 'CREATED' && $shipment->trackingEvents()->count() === 0);
            if ($newStatus !== 'IN_TRANSIT' && ! $isInitialCreated) {
                throw new InvalidStatusTransitionException(
                    "Status {$newStatus} sudah aktif. Pembaruan checkpoint berstatus sama hanya diizinkan saat status kargo IN_TRANSIT."
                );
            }
        }

        return DB::transaction(function () use ($shipment, $data, $actor, $newStatus) {
            // Auto-fill location metadata if location_id is provided and fields are omitted
            if (! empty($data['location_id'])) {
                $location = Location::find($data['location_id']);
                if ($location) {
                    $data['location_name'] = $data['location_name'] ?? $location->name;
                    $data['latitude'] = $data['latitude'] ?? $location->latitude;
                    $data['longitude'] = $data['longitude'] ?? $location->longitude;
                }
            }

            $data['created_by'] = $actor->id;
            $data['event_time'] = $data['event_time'] ?? now();

            $event = $shipment->trackingEvents()->create($data);

            $updateData = ['current_status' => $newStatus];
            if ($newStatus === 'DELIVERED') {
                $updateData['estimated_arrival'] = $data['event_time'];
            }
            $shipment->update($updateData);

            return $event->load(['creator', 'location', 'shipment']);
        });
    }

    /**
     * Retrieve complete ordered timeline of tracking events for a shipment.
     *
     * @return Collection<int, TrackingEvent>
     */
    public function getShipmentTimeline(Shipment $shipment): Collection
    {
        return $shipment->trackingEvents()
            ->with(['creator', 'location'])
            ->orderBy('event_time', 'asc')
            ->get();
    }
}
