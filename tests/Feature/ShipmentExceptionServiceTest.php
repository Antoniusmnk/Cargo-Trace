<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Services\ExceptionService;
use App\Services\ShipmentService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentExceptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExceptionService $exceptionService;
    private ShipmentService $shipmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exceptionService = app(ExceptionService::class);
        $this->shipmentService = app(ShipmentService::class);
    }

    private function createBaselineShipment(): array
    {
        $operator = User::factory()->create([
            'email' => 'operator.exc@cargotrace.test',
            'role' => 'operator',
            'status' => 'active',
        ]);

        $customer = User::factory()->create([
            'email' => 'customer.exc@cargotrace.test',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $carrier = Carrier::create([
            'code' => 'MSC',
            'name' => 'Mediterranean Shipping Company',
            'status' => 'active',
        ]);

        $origin = Location::create([
            'code' => 'IDSUB',
            'name' => 'Port of Tanjung Perak',
            'type' => 'port',
            'city' => 'Surabaya',
            'country' => 'Indonesia',
            'latitude' => -7.1989000,
            'longitude' => 112.7328000,
        ]);

        $destination = Location::create([
            'code' => 'NLRTM',
            'name' => 'Port of Rotterdam',
            'type' => 'port',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'latitude' => 51.9244000,
            'longitude' => 4.4777000,
        ]);

        $expectedArrival = Carbon::now()->addDays(10);

        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Specialty Arabica Coffee Beans',
            'expected_departure' => Carbon::now()->addDays(2),
            'expected_arrival' => $expectedArrival,
            'estimated_arrival' => $expectedArrival,
            'is_delayed' => false,
            'delay_hours' => 0,
            'delay_severity' => 'NONE',
        ]);

        return compact('operator', 'customer', 'carrier', 'origin', 'destination', 'shipment');
    }

    public function test_create_exception_records_disruption_and_auto_updates_shipment_eta(): void
    {
        extract($this->createBaselineShipment());

        $originalArrival = $shipment->expected_arrival;

        $exception = $this->exceptionService->createException($shipment, [
            'exception_type' => 'WEATHER_DELAY',
            'severity' => 'MEDIUM',
            'status' => 'OPEN',
            'impact_hours' => 12,
            'detected_at' => now(),
            'description' => 'Tropical typhoon warning delaying vessel crossing in South China Sea.',
        ], $operator);

        $this->assertInstanceOf(ShipmentException::class, $exception);
        $this->assertSame('WEATHER_DELAY', $exception->exception_type);
        $this->assertSame('MEDIUM', $exception->severity);
        $this->assertSame('OPEN', $exception->status);
        $this->assertSame(12, $exception->impact_hours);
        $this->assertSame($operator->id, $exception->reported_by);

        // Verifikasi database shipment_exceptions
        $this->assertDatabaseHas('shipment_exceptions', [
            'id' => $exception->id,
            'shipment_id' => $shipment->id,
            'exception_type' => 'WEATHER_DELAY',
            'status' => 'OPEN',
            'impact_hours' => 12,
        ]);

        // Verifikasi shipment ETA ter-update otomatis mundur 12 jam
        $shipment->refresh();
        $this->assertTrue($shipment->is_delayed);
        $this->assertSame(12, $shipment->delay_hours);
        $this->assertSame('MEDIUM', $shipment->delay_severity);
        $this->assertTrue(
            $shipment->estimated_arrival->equalTo((clone $originalArrival)->addHours(12))
        );
    }

    public function test_update_exception_status_to_resolved_records_timestamp(): void
    {
        extract($this->createBaselineShipment());

        $exception = $this->exceptionService->createException($shipment, [
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'impact_hours' => 24,
            'description' => 'Random customs physical inspection at terminal.',
        ], $operator);

        $this->assertSame('OPEN', $exception->status);
        $this->assertNull($exception->resolved_at);

        // 1. Transisi ke INVESTIGATING
        $investigating = $this->exceptionService->updateStatus($exception, 'INVESTIGATING');
        $this->assertSame('INVESTIGATING', $investigating->status);
        $this->assertNull($investigating->resolved_at);

        // 2. Transisi ke RESOLVED
        $resolved = $this->exceptionService->updateStatus(
            $investigating,
            'RESOLVED',
            'Inspection completed successfully, green lane release issued.'
        );

        $this->assertSame('RESOLVED', $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
        $this->assertSame(
            'Inspection completed successfully, green lane release issued.',
            $resolved->resolution_notes
        );

        $this->assertDatabaseHas('shipment_exceptions', [
            'id' => $exception->id,
            'status' => 'RESOLVED',
        ]);
    }

    public function test_resolved_exception_cannot_be_reopened(): void
    {
        extract($this->createBaselineShipment());

        $exception = $this->exceptionService->createException($shipment, [
            'exception_type' => 'PORT_CONGESTION',
            'severity' => 'MEDIUM',
            'status' => 'OPEN',
            'impact_hours' => 6,
            'description' => 'Berth waiting queue.',
        ], $operator);

        $this->exceptionService->updateStatus($exception, 'RESOLVED', 'Berth allocated.');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Gangguan kargo dengan status RESOLVED bersifat final');

        // Coba buka kembali exception yang sudah RESOLVED
        $this->exceptionService->updateStatus($exception, 'OPEN');
    }

    public function test_invalid_transition_throws_domain_exception(): void
    {
        extract($this->createBaselineShipment());

        $exception = $this->exceptionService->createException($shipment, [
            'exception_type' => 'DAMAGED',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'impact_hours' => 0,
            'description' => 'Container seal broken alert.',
        ], $operator);

        // Transisi ke INVESTIGATING
        $this->exceptionService->updateStatus($exception, 'INVESTIGATING');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Transisi status exception dari INVESTIGATING ke OPEN tidak valid');

        // Coba mundur dari INVESTIGATING ke OPEN
        $this->exceptionService->updateStatus($exception, 'OPEN');
    }

    public function test_get_exceptions_for_shipment_ordered_by_detected_at(): void
    {
        extract($this->createBaselineShipment());

        $now = Carbon::now();

        $exc1 = $this->exceptionService->createException($shipment, [
            'exception_type' => 'WEATHER_DELAY',
            'severity' => 'LOW',
            'detected_at' => (clone $now)->subHours(10),
            'description' => 'Initial weather issue',
        ], $operator);

        $exc2 = $this->exceptionService->createException($shipment, [
            'exception_type' => 'PORT_CONGESTION',
            'severity' => 'HIGH',
            'detected_at' => (clone $now)->subHours(2),
            'description' => 'Later congestion issue',
        ], $operator);

        $exceptions = $this->exceptionService->getExceptionsForShipment($shipment);

        $this->assertCount(2, $exceptions);
        // Terurut descending (terbaru di atas)
        $this->assertEquals($exc2->id, $exceptions->first()->id);
        $this->assertEquals($exc1->id, $exceptions->last()->id);
    }

    public function test_scopes_open_and_resolved(): void
    {
        extract($this->createBaselineShipment());

        $this->exceptionService->createException($shipment, [
            'exception_type' => 'WEATHER_DELAY',
            'status' => 'OPEN',
            'description' => 'Open disruption',
        ], $operator);

        $excInvestigating = $this->exceptionService->createException($shipment, [
            'exception_type' => 'CUSTOMS_HOLD',
            'status' => 'OPEN',
            'description' => 'Under investigation',
        ], $operator);
        $this->exceptionService->updateStatus($excInvestigating, 'INVESTIGATING');

        $excResolved = $this->exceptionService->createException($shipment, [
            'exception_type' => 'DOCUMENTATION_ISSUE',
            'status' => 'OPEN',
            'description' => 'Resolved issue',
        ], $operator);
        $this->exceptionService->updateStatus($excResolved, 'RESOLVED', 'Fixed');

        // Query scopes
        $this->assertSame(2, ShipmentException::open()->count());
        $this->assertSame(1, ShipmentException::resolved()->count());

        // Relasi Shipment
        $this->assertSame(2, $shipment->openExceptions()->count());
        $this->assertSame(3, $shipment->exceptions()->count());
    }
}
