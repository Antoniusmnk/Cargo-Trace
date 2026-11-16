<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TrackingEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->first()
            ?? User::where('role', 'operator')->first();

        if (! $operator) {
            $operator = User::create([
                'name' => 'Logistics Operations Officer',
                'email' => 'operator@cargotrace.com',
                'password' => bcrypt('password'),
                'role' => 'operator',
                'status' => 'active',
            ]);
        }

        // Shipment 1: CT-202611-0001
        $shipment1 = Shipment::where('shipment_number', 'CT-202611-0001')->first();
        if ($shipment1) {
            $locPriok = Location::find($shipment1->origin_id);
            $eventTime1 = Carbon::parse($shipment1->expected_departure)->subDay();

            // Clear existing events for idempotency
            TrackingEvent::where('shipment_id', $shipment1->id)->delete();

            TrackingEvent::create([
                'shipment_id' => $shipment1->id,
                'location_id' => $locPriok?->id,
                'created_by' => $operator->id,
                'status' => 'CREATED',
                'event_time' => $eventTime1,
                'location_name' => 'Tanjung Priok Container Terminal 1, Jakarta',
                'latitude' => $locPriok?->latitude ?? -6.1018800,
                'longitude' => $locPriok?->longitude ?? 106.8833800,
                'description' => 'Booking dan manifes kargo berhasil didaftarkan di sistem pelacakan.',
            ]);
        }

        // Shipment 2: CT-202611-0002
        $shipment2 = Shipment::where('shipment_number', 'CT-202611-0002')->first();
        if ($shipment2) {
            $locPerak = Location::find($shipment2->origin_id);
            $baseTime = Carbon::parse($shipment2->expected_departure)->subDays(2);

            TrackingEvent::where('shipment_id', $shipment2->id)->delete();

            // Event 1: CREATED
            TrackingEvent::create([
                'shipment_id' => $shipment2->id,
                'location_id' => $locPerak?->id,
                'created_by' => $operator->id,
                'status' => 'CREATED',
                'event_time' => (clone $baseTime)->subHours(6),
                'location_name' => 'Pelabuhan Tanjung Perak Terminal Jamrud, Surabaya',
                'latitude' => $locPerak?->latitude ?? -7.1989000,
                'longitude' => $locPerak?->longitude ?? 112.7328000,
                'description' => 'Pemesanan kargo ekspor biji kopi didaftarkan ke sistem.',
            ]);

            // Event 2: BOOKED (selang beberapa jam)
            TrackingEvent::create([
                'shipment_id' => $shipment2->id,
                'location_id' => $locPerak?->id,
                'created_by' => $operator->id,
                'status' => 'BOOKED',
                'event_time' => (clone $baseTime)->subHours(2),
                'location_name' => 'Pelabuhan Tanjung Perak Terminal Jamrud, Surabaya',
                'latitude' => $locPerak?->latitude ?? -7.1989000,
                'longitude' => $locPerak?->longitude ?? 112.7328000,
                'description' => 'Slot kontainer pelayaran MSC resmi dikonfirmasi untuk rute Surabaya - Rotterdam.',
            ]);
        }
    }
}
