<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Generate a unique shipment tracking number in format CT-YYYYMM-XXXX.
     */
    public function generateShipmentNumber(): string
    {
        do {
            $random = str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $number = 'CT-' . date('Ym') . '-' . $random;
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }

    /**
     * Create a new shipment and its manifest items atomically.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createShipment(array $data, array $items = []): Shipment
    {
        return DB::transaction(function () use ($data, $items) {
            if (empty($data['shipment_number'])) {
                $data['shipment_number'] = $this->generateShipmentNumber();
            }

            if (empty($data['current_status'])) {
                $data['current_status'] = 'CREATED';
            }

            if (! empty($items)) {
                if (! isset($data['total_weight_kg']) || (float) $data['total_weight_kg'] <= 0) {
                    $data['total_weight_kg'] = array_sum(array_column($items, 'weight_kg'));
                }
                if (! isset($data['total_volume_cbm']) || (float) $data['total_volume_cbm'] <= 0) {
                    $data['total_volume_cbm'] = array_sum(array_column($items, 'volume_cbm'));
                }
            }

            $shipment = Shipment::create($data);

            if (! empty($items)) {
                $shipment->items()->createMany($items);
            }

            return $shipment->load(['items', 'carrier', 'origin', 'destination', 'customer']);
        });
    }

    /**
     * Update an existing shipment with business rule validation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \DomainException
     */
    public function updateShipment(Shipment $shipment, array $data): Shipment
    {
        if ($shipment->current_status === 'DELIVERED') {
            throw new \DomainException('Status kargo sudah DELIVERED dan tidak dapat diubah tanpa prosedur khusus.');
        }

        return DB::transaction(function () use ($shipment, $data) {
            $shipment->update($data);

            return $shipment->refresh()->load(['items', 'carrier', 'origin', 'destination', 'customer']);
        });
    }

    /**
     * Delete a shipment and cascade its items if business rules permit.
     *
     * @throws \DomainException
     */
    public function deleteShipment(Shipment $shipment): bool
    {
        if (in_array($shipment->current_status, ['IN_TRANSIT', 'DELIVERED'], true)) {
            throw new \DomainException('Kargo yang sedang dalam perjalanan atau telah terkirim tidak dapat dihapus.');
        }

        return DB::transaction(function () use ($shipment) {
            return (bool) $shipment->delete();
        });
    }

    /**
     * Get paginated shipments scoped by user role and query filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedShipmentsForUser(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Shipment::with(['carrier', 'origin', 'destination', 'customer']);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('shipment_number', 'like', "%{$search}%");
        }

        if (! empty($filters['status'])) {
            $query->where('current_status', $filters['status']);
        }

        if (! empty($filters['carrier_id'])) {
            $query->where('carrier_id', $filters['carrier_id']);
        }

        if (isset($filters['delayed']) && $filters['delayed'] !== '') {
            $query->where('is_delayed', filter_var($filters['delayed'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest('created_at')->paginate($perPage);
    }
}
