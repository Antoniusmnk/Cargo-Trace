<?php

namespace Tests\Feature\Api;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ShipmentExceptionSeeder;
use Database\Seeders\ShipmentSeeder;
use Database\Seeders\TrackingEventSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExceptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
            TrackingEventSeeder::class,
            ShipmentExceptionSeeder::class,
        ]);
    }

    public function test_customer_can_view_own_shipment_exceptions(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        Sanctum::actingAs($customer);

        $response = $this->getJson("/api/shipments/{$shipment->id}/exceptions");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'shipment_id' => $shipment->id,
                    'shipment_number' => 'CT-202611-0001',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'shipment_id',
                    'shipment_number',
                    'current_status',
                    'is_delayed',
                    'delay_hours',
                    'delay_severity',
                    'exceptions' => [
                        '*' => [
                            'id',
                            'shipment_id',
                            'exception_type',
                            'severity',
                            'status',
                            'impact_hours',
                            'description',
                        ],
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.exceptions'));
    }

    public function test_customer_cannot_view_other_customer_exceptions_returns_403(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $otherCustomer = User::factory()->create([
            'email' => 'other.client@example.test',
            'role' => 'customer',
            'status' => 'active',
        ]);

        Sanctum::actingAs($otherCustomer);

        $response = $this->getJson("/api/shipments/{$shipment->id}/exceptions");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk melihat kendala kargo ini.',
            ]);
    }

    public function test_customer_cannot_report_exception_returns_403(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        Sanctum::actingAs($customer);

        $payload = [
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'description' => 'Customer trying to file exception',
        ];

        $response = $this->postJson("/api/shipments/{$shipment->id}/exceptions", $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Customer tidak diizinkan melaporkan kendala kargo.',
            ]);
    }

    public function test_operator_can_report_exception_and_triggers_eta_update(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        Sanctum::actingAs($operator);

        $payload = [
            'exception_type' => 'PORT_CONGESTION',
            'severity' => 'HIGH',
            'impact_hours' => 28,
            'description' => 'Terminal berth congestion at destination Singapore Port.',
        ];

        $response = $this->postJson("/api/shipments/{$shipment->id}/exceptions", $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Kendala kargo berhasil dicatat dan dampak jadwal diperhitungkan.',
            ])
            ->assertJsonPath('data.exception.exception_type', 'PORT_CONGESTION')
            ->assertJsonPath('data.exception.severity', 'HIGH')
            ->assertJsonPath('data.exception.impact_hours', 28)
            ->assertJsonPath('data.shipment.is_delayed', true);

        $this->assertDatabaseHas('shipment_exceptions', [
            'shipment_id' => $shipment->id,
            'exception_type' => 'PORT_CONGESTION',
            'impact_hours' => 28,
            'reported_by' => $operator->id,
        ]);

        $shipment->refresh();
        $this->assertTrue($shipment->is_delayed);
        $this->assertGreaterThanOrEqual(28, $shipment->delay_hours);
    }

    public function test_operator_can_resolve_exception_with_resolution_notes(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        // Exception 1 dari seeder adalah WEATHER_DELAY pada CT-202611-0001 berstatus INVESTIGATING
        $exception = ShipmentException::where('status', 'INVESTIGATING')->firstOrFail();

        Sanctum::actingAs($operator);

        $payload = [
            'status' => 'RESOLVED',
            'resolution_notes' => 'Typhoon dissipated. Vessel returned to maximum cruise speed.',
        ];

        $response = $this->patchJson("/api/exceptions/{$exception->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Status kendala kargo berhasil diperbarui.',
            ])
            ->assertJsonPath('data.status', 'RESOLVED')
            ->assertJsonPath('data.resolution_notes', 'Typhoon dissipated. Vessel returned to maximum cruise speed.');

        $this->assertDatabaseHas('shipment_exceptions', [
            'id' => $exception->id,
            'status' => 'RESOLVED',
        ]);
        $this->assertNotNull($exception->refresh()->resolved_at);
    }

    public function test_cannot_mutate_resolved_exception_returns_422(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        // Exception 3 dari seeder berstatus RESOLVED
        $exception = ShipmentException::where('status', 'RESOLVED')->firstOrFail();

        Sanctum::actingAs($operator);

        $response = $this->patchJson("/api/exceptions/{$exception->id}", [
            'status' => 'OPEN',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('RESOLVED bersifat final', $response->json('message'));
    }

    public function test_operator_can_recalculate_shipment_eta(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        Sanctum::actingAs($operator);

        // Uji dengan new_estimated_arrival eksplisit
        $newEta = now()->addDays(8)->toIso8601String();
        $response = $this->postJson("/api/shipments/{$shipment->id}/recalculate-eta", [
            'new_estimated_arrival' => $newEta,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kalkulasi ulang jadwal kedatangan kargo (ETA) berhasil.',
            ])
            ->assertJsonPath('data.shipment_id', $shipment->id)
            ->assertJsonPath('data.is_delayed', true);

        // Uji tanpa new_estimated_arrival (recalculate existing)
        $resRecalc = $this->postJson("/api/shipments/{$shipment->id}/recalculate-eta", []);
        $resRecalc->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_admin_and_operator_can_list_all_exceptions_with_filters(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);

        // 1. Ambil seluruh exceptions
        $response = $this->getJson('/api/exceptions');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'total'],
                'links',
            ]);
        $this->assertCount(3, $response->json('data'));

        // 2. Filter berdasarkan status OPEN
        $resOpen = $this->getJson('/api/exceptions?status=OPEN');
        $resOpen->assertStatus(200);
        $this->assertCount(1, $resOpen->json('data'));
        $this->assertSame('OPEN', $resOpen->json('data.0.status'));

        // 3. Filter berdasarkan exception_type CUSTOMS_HOLD
        $resCustoms = $this->getJson('/api/exceptions?exception_type=CUSTOMS_HOLD');
        $resCustoms->assertStatus(200);
        $this->assertCount(1, $resCustoms->json('data'));
        $this->assertSame('CUSTOMS_HOLD', $resCustoms->json('data.0.exception_type'));
    }

    public function test_customer_cannot_access_global_exceptions_index(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/exceptions');
        $response->assertStatus(403);
    }
}
