<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShipmentExceptionSeeder extends Seeder
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

        $etaService = app(EtaService::class);
        $now = Carbon::now();

        // 1. Kasus 1: Shipment 1 (Telecommunications) - WEATHER_DELAY (Medium, 12 jam)
        $shipment1 = Shipment::where('shipment_number', 'CT-202611-0001')->first();
        if ($shipment1) {
            ShipmentException::updateOrCreate(
                [
                    'shipment_id' => $shipment1->id,
                    'exception_type' => 'WEATHER_DELAY',
                ],
                [
                    'reported_by' => $operator->id,
                    'severity' => 'MEDIUM',
                    'status' => 'INVESTIGATING',
                    'detected_at' => (clone $now)->subHours(8),
                    'impact_hours' => 12,
                    'description' => 'MV Maersk Mc-Kinney Moller memperlambat kecepatan kapal karena badai tropis di Laut Cina Selatan.',
                ]
            );

            // Update shipment ETA (+12 jam)
            $newEta1 = (clone $shipment1->expected_arrival)->addHours(12);
            $etaService->updateShipmentEta($shipment1, $newEta1, 'Adjusted due to WEATHER_DELAY');
        }

        // 2. Kasus 2: Shipment 2 (Coffee Beans) - CUSTOMS_HOLD (High, 36 jam)
        $shipment2 = Shipment::where('shipment_number', 'CT-202611-0002')->first();
        if ($shipment2) {
            ShipmentException::updateOrCreate(
                [
                    'shipment_id' => $shipment2->id,
                    'exception_type' => 'CUSTOMS_HOLD',
                ],
                [
                    'reported_by' => $operator->id,
                    'severity' => 'HIGH',
                    'status' => 'OPEN',
                    'detected_at' => (clone $now)->subHours(4),
                    'impact_hours' => 36,
                    'description' => 'Pemeriksaan acak fitosanitari oleh karantina pelabuhan Rotterdam terhadap kontainer kopi mentah.',
                ]
            );

            // Update shipment ETA (+36 jam)
            $newEta2 = (clone $shipment2->expected_arrival)->addHours(36);
            $etaService->updateShipmentEta($shipment2, $newEta2, 'Adjusted due to CUSTOMS_HOLD');
        }

        // 3. Kasus 3: Shipment 3 (Solar Panels) - DOCUMENTATION_ISSUE (Low, Resolved)
        $shipment3 = Shipment::where('shipment_number', 'CT-202611-0003')->first();
        if ($shipment3) {
            ShipmentException::updateOrCreate(
                [
                    'shipment_id' => $shipment3->id,
                    'exception_type' => 'DOCUMENTATION_ISSUE',
                ],
                [
                    'reported_by' => $operator->id,
                    'severity' => 'LOW',
                    'status' => 'RESOLVED',
                    'detected_at' => (clone $now)->subHours(6),
                    'resolved_at' => (clone $now)->subHour(),
                    'impact_hours' => 0,
                    'description' => 'Koreksi nomor HS Code pada sertifikat asal (Certificate of Origin).',
                    'resolution_notes' => 'Revisi dokumen telah dikirimkan dan disetujui otoritas kepabeanan pelabuhan.',
                ]
            );
        }
    }
}
