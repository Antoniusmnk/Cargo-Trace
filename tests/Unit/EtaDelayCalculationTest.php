<?php

namespace Tests\Unit;

use App\Models\Shipment;
use App\Services\EtaService;
use Carbon\Carbon;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ShipmentSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtaDelayCalculationTest extends TestCase
{
    use RefreshDatabase;

    private EtaService $etaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->etaService = new EtaService();
    }

    public function test_shipment_is_not_delayed_when_eta_is_before_or_equal_expected_arrival(): void
    {
        $expected = Carbon::parse('2026-12-01 12:00:00');

        // Case 1: Estimated arrival is null
        $resultNull = $this->etaService->calculateDelay($expected, null);
        $this->assertFalse($resultNull['is_delayed']);
        $this->assertSame(0, $resultNull['delay_hours']);
        $this->assertSame('NONE', $resultNull['delay_severity']);

        // Case 2: Estimated arrival is earlier than expected arrival
        $early = Carbon::parse('2026-12-01 08:00:00');
        $resultEarly = $this->etaService->calculateDelay($expected, $early);
        $this->assertFalse($resultEarly['is_delayed']);
        $this->assertSame(0, $resultEarly['delay_hours']);
        $this->assertSame('NONE', $resultEarly['delay_severity']);

        // Case 3: Estimated arrival is exactly equal to expected arrival
        $exact = Carbon::parse('2026-12-01 12:00:00');
        $resultExact = $this->etaService->calculateDelay($expected, $exact);
        $this->assertFalse($resultExact['is_delayed']);
        $this->assertSame(0, $resultExact['delay_hours']);
        $this->assertSame('NONE', $resultExact['delay_severity']);
    }

    public function test_shipment_delay_severity_low_for_0_to_6_hours(): void
    {
        $expected = Carbon::parse('2026-12-01 12:00:00');

        // Keterlambatan 4 jam (Low)
        $delayed4h = Carbon::parse('2026-12-01 16:00:00');
        $result = $this->etaService->calculateDelay($expected, $delayed4h);

        $this->assertTrue($result['is_delayed']);
        $this->assertSame(4, $result['delay_hours']);
        $this->assertSame(240, $result['delay_minutes']);
        $this->assertSame('LOW', $result['delay_severity']);

        // Keterlambatan 6 jam (Batas atas Low)
        $delayed6h = Carbon::parse('2026-12-01 18:00:00');
        $result6 = $this->etaService->calculateDelay($expected, $delayed6h);

        $this->assertTrue($result6['is_delayed']);
        $this->assertSame(6, $result6['delay_hours']);
        $this->assertSame('LOW', $result6['delay_severity']);
    }

    public function test_shipment_delay_severity_medium_for_7_to_24_hours(): void
    {
        $expected = Carbon::parse('2026-12-01 12:00:00');

        // Keterlambatan 14 jam (Medium)
        $delayed14h = Carbon::parse('2026-12-02 02:00:00');
        $result = $this->etaService->calculateDelay($expected, $delayed14h);

        $this->assertTrue($result['is_delayed']);
        $this->assertSame(14, $result['delay_hours']);
        $this->assertSame('MEDIUM', $result['delay_severity']);

        // Keterlambatan 24 jam (Batas atas Medium)
        $delayed24h = Carbon::parse('2026-12-02 12:00:00');
        $result24 = $this->etaService->calculateDelay($expected, $delayed24h);

        $this->assertTrue($result24['is_delayed']);
        $this->assertSame(24, $result24['delay_hours']);
        $this->assertSame('MEDIUM', $result24['delay_severity']);
    }

    public function test_shipment_delay_severity_high_for_more_than_24_hours(): void
    {
        $expected = Carbon::parse('2026-12-01 12:00:00');

        // Keterlambatan 36 jam (> 24 jam -> High)
        $delayed36h = Carbon::parse('2026-12-03 00:00:00');
        $result = $this->etaService->calculateDelay($expected, $delayed36h);

        $this->assertTrue($result['is_delayed']);
        $this->assertSame(36, $result['delay_hours']);
        $this->assertSame('HIGH', $result['delay_severity']);
    }

    public function test_delay_threshold_is_configurable_dynamically(): void
    {
        $expected = Carbon::parse('2026-12-01 12:00:00');
        $delayed30h = Carbon::parse('2026-12-02 18:00:00'); // 30 jam

        // 1. Secara default 30 jam adalah HIGH (> 24 jam)
        $defaultResult = $this->etaService->calculateDelay($expected, $delayed30h);
        $this->assertSame('HIGH', $defaultResult['delay_severity']);

        // 2. Ubah konfigurasi medium.max_hours menjadi 48 jam secara dinamis
        config(['cargotrace.delay_thresholds.medium.max_hours' => 48]);

        // 3. Evaluasi ulang: 30 jam sekarang harus berstatus MEDIUM (karena <= 48 jam)
        $updatedResult = $this->etaService->calculateDelay($expected, $delayed30h);
        $this->assertSame('MEDIUM', $updatedResult['delay_severity']);
    }

    public function test_eta_service_updates_shipment_model_correctly(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        // Jadwal tiba awal
        $expectedArrival = $shipment->expected_arrival;

        // Update ETA mundur 10 jam (Medium severity)
        $newEta = (clone $expectedArrival)->addHours(10);
        $updated = $this->etaService->updateShipmentEta($shipment, $newEta, 'Vessel delayed due to adverse weather');

        $this->assertTrue($updated->is_delayed);
        $this->assertSame(10, $updated->delay_hours);
        $this->assertSame('MEDIUM', $updated->delay_severity);
        $this->assertTrue($updated->estimated_arrival->equalTo($newEta));

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'is_delayed' => true,
            'delay_hours' => 10,
            'delay_severity' => 'MEDIUM',
        ]);

        // Test recalculateShipmentDelay
        $recalculated = $this->etaService->recalculateShipmentDelay($updated);
        $this->assertTrue($recalculated->is_delayed);
        $this->assertSame(10, $recalculated->delay_hours);
        $this->assertSame('MEDIUM', $recalculated->delay_severity);
    }

    public function test_shipment_delay_badge_and_status_text_accessors(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        // 1. Kondisi On Time
        $shipment->update([
            'is_delayed' => false,
            'delay_hours' => 0,
            'delay_severity' => 'NONE',
        ]);
        $this->assertStringContainsString('emerald', $shipment->delay_badge_color);
        $this->assertSame('On Time', $shipment->delay_status_text);

        // 2. Kondisi LOW
        $shipment->update([
            'is_delayed' => true,
            'delay_hours' => 3,
            'delay_severity' => 'LOW',
        ]);
        $this->assertStringContainsString('sky', $shipment->delay_badge_color);
        $this->assertSame('Delayed (+3 hrs) [LOW]', $shipment->delay_status_text);

        // 3. Kondisi MEDIUM
        $shipment->update([
            'is_delayed' => true,
            'delay_hours' => 12,
            'delay_severity' => 'MEDIUM',
        ]);
        $this->assertStringContainsString('amber', $shipment->delay_badge_color);
        $this->assertSame('Delayed (+12 hrs) [MEDIUM]', $shipment->delay_status_text);

        // 4. Kondisi HIGH
        $shipment->update([
            'is_delayed' => true,
            'delay_hours' => 30,
            'delay_severity' => 'HIGH',
        ]);
        $this->assertStringContainsString('rose', $shipment->delay_badge_color);
        $this->assertSame('Delayed (+30 hrs) [HIGH]', $shipment->delay_status_text);
    }
}
