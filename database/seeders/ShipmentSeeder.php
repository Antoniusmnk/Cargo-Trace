<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->first()
            ?? User::firstOrCreate(
                ['email' => 'customer@cargotrace.com'],
                ['name' => 'Global Importer Client', 'password' => bcrypt('password'), 'role' => 'customer', 'status' => 'active']
            );

        $carrierMsk = Carrier::where('code', 'MSK')->first();
        $carrierMsc = Carrier::where('code', 'MSC')->first();
        $carrierCma = Carrier::where('code', 'CMA')->first();

        $locPriok = Location::where('code', 'IDTPP')->first();
        $locPerak = Location::where('code', 'IDSUB')->first();
        $locSingapore = Location::where('code', 'SGSIN')->first();
        $locShanghai = Location::where('code', 'CNSHG')->first();
        $locRotterdam = Location::where('code', 'NLRTM')->first();

        $now = Carbon::now();

        $shipmentsData = [
            [
                'shipment' => [
                    'shipment_number' => 'CT-202611-0001',
                    'customer_id' => $customer->id,
                    'carrier_id' => $carrierMsk->id,
                    'origin_id' => $locPriok->id,
                    'destination_id' => $locSingapore->id,
                    'cargo_type' => 'Telecommunication & Electronics',
                    'total_weight_kg' => 12500.00,
                    'total_volume_cbm' => 45.50,
                    'expected_departure' => (clone $now)->addDay(),
                    'expected_arrival' => (clone $now)->addDays(4),
                    'estimated_arrival' => (clone $now)->addDays(4),
                    'current_status' => 'CREATED',
                    'is_delayed' => false,
                    'delay_hours' => 0,
                    'delay_severity' => 'NONE',
                    'notes' => 'High-priority networking hardware shipment to regional distribution hub.',
                ],
                'items' => [
                    [
                        'item_name' => 'Fiber Optic Transceivers',
                        'quantity' => 50,
                        'weight_kg' => 4500.00,
                        'volume_cbm' => 15.00,
                        'package_type' => 'Pallet',
                        'hs_code' => '8517.62',
                    ],
                    [
                        'item_name' => 'Server Rack Cabinets',
                        'quantity' => 20,
                        'weight_kg' => 8000.00,
                        'volume_cbm' => 30.50,
                        'package_type' => '20ft Container',
                        'hs_code' => '8471.70',
                    ],
                ],
            ],
            [
                'shipment' => [
                    'shipment_number' => 'CT-202611-0002',
                    'customer_id' => $customer->id,
                    'carrier_id' => $carrierMsc->id,
                    'origin_id' => $locPerak->id,
                    'destination_id' => $locRotterdam->id,
                    'cargo_type' => 'Specialty Arabica Coffee Beans',
                    'total_weight_kg' => 18000.00,
                    'total_volume_cbm' => 60.00,
                    'expected_departure' => (clone $now)->addDays(2),
                    'expected_arrival' => (clone $now)->addDays(20),
                    'estimated_arrival' => (clone $now)->addDays(20),
                    'current_status' => 'BOOKED',
                    'is_delayed' => false,
                    'delay_hours' => 0,
                    'delay_severity' => 'NONE',
                    'notes' => 'Direct sea freight container booked for European roasting facility.',
                ],
                'items' => [
                    [
                        'item_name' => 'Green Coffee Gayo Super Grade',
                        'quantity' => 300,
                        'weight_kg' => 18000.00,
                        'volume_cbm' => 60.00,
                        'package_type' => 'Jute Bag',
                        'hs_code' => '0901.11',
                    ],
                ],
            ],
            [
                'shipment' => [
                    'shipment_number' => 'CT-202611-0003',
                    'customer_id' => $customer->id,
                    'carrier_id' => $carrierCma->id,
                    'origin_id' => $locShanghai->id,
                    'destination_id' => $locPriok->id,
                    'cargo_type' => 'Solar PV Modules & Inverters',
                    'total_weight_kg' => 24000.00,
                    'total_volume_cbm' => 72.00,
                    'expected_departure' => (clone $now)->subDay(),
                    'expected_arrival' => (clone $now)->addDays(5),
                    'estimated_arrival' => (clone $now)->addDays(5),
                    'current_status' => 'CREATED',
                    'is_delayed' => false,
                    'delay_hours' => 0,
                    'delay_severity' => 'NONE',
                    'notes' => 'Import consignment for National Renewable Energy Infrastructure.',
                ],
                'items' => [
                    [
                        'item_name' => 'Tier-1 Monocrystalline Solar Panels',
                        'quantity' => 600,
                        'weight_kg' => 24000.00,
                        'volume_cbm' => 72.00,
                        'package_type' => '40ft High Cube Container',
                        'hs_code' => '8541.43',
                    ],
                ],
            ],
        ];

        foreach ($shipmentsData as $data) {
            $shipment = Shipment::updateOrCreate(
                ['shipment_number' => $data['shipment']['shipment_number']],
                $data['shipment']
            );

            // Recreate items to keep idempotent
            $shipment->items()->delete();
            $shipment->items()->createMany($data['items']);
        }
    }
}
