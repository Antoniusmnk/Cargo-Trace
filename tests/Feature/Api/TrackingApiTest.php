<?php

namespace Tests\Feature\Api;

use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ShipmentSeeder;
use Database\Seeders\TrackingEventSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrackingApiTest extends TestCase
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
        ]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/shipments/1/tracking')->assertStatus(401);
        $this->postJson('/api/shipments/1/tracking', [])->assertStatus(401);
    }

    public function test_customer_can_view_own_shipment_tracking_timeline(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        Sanctum::actingAs($customer);

        $response = $this->getJson("/api/shipments/{$shipment->id}/tracking");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Riwayat perjalanan kargo berhasil diambil.',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'shipment_number' => 'CT-202611-0001',
                    'current_status' => 'CREATED',
                    'carrier' => 'Maersk Line',
                    'origin' => 'Port of Tanjung Priok',
                    'destination' => 'Port of Singapore',
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
                    'carrier',
                    'origin',
                    'destination',
                    'timeline' => [
                        '*' => [
                            'id',
                            'shipment_id',
                            'status',
                            'event_time',
                            'location_name',
                            'description',
                            'creator',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data.timeline'));
        $this->assertSame('CREATED', $response->json('data.timeline.0.status'));
    }

    public function test_customer_cannot_view_other_customer_tracking_timeline(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $otherCustomer = User::factory()->create([
            'email' => 'competitor@client.test',
            'role' => 'customer',
            'status' => 'active',
        ]);

        Sanctum::actingAs($otherCustomer);

        $response = $this->getJson("/api/shipments/{$shipment->id}/tracking");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk melihat riwayat pelacakan kargo ini.',
            ]);
    }

    public function test_customer_cannot_add_tracking_event(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        Sanctum::actingAs($customer);

        $payload = [
            'status' => 'BOOKED',
            'event_time' => now()->toIso8601String(),
            'location_name' => 'Tanjung Priok, Jakarta',
            'description' => 'Customer trying to update checkpoint',
        ];

        $response = $this->postJson("/api/shipments/{$shipment->id}/tracking", $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Customer tidak diizinkan menambahkan tracking event.',
            ]);
    }

    public function test_operator_can_add_valid_tracking_event_and_updates_status(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $this->assertSame('CREATED', $shipment->current_status);

        Sanctum::actingAs($operator);

        $payload = [
            'status' => 'BOOKED',
            'event_time' => now()->addHour()->toIso8601String(),
            'location_id' => $shipment->origin_id,
            'location_name' => 'Tanjung Priok Yard 2, Jakarta',
            'latitude' => -6.1018800,
            'longitude' => 106.8833800,
            'description' => 'Slot booking pelayaran Maersk Line resmi dikonfirmasi operator logistik.',
        ];

        $response = $this->postJson("/api/shipments/{$shipment->id}/tracking", $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Tracking event berhasil dicatat dan status kargo diperbarui.',
            ])
            ->assertJsonPath('data.status', 'BOOKED')
            ->assertJsonPath('data.created_by', $operator->id)
            ->assertJsonPath('data.location_name', 'Tanjung Priok Yard 2, Jakarta');

        // Verifikasi database tracking_events
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'status' => 'BOOKED',
            'created_by' => $operator->id,
            'location_name' => 'Tanjung Priok Yard 2, Jakarta',
        ]);

        // Verifikasi status pada database shipments berubah menjadi BOOKED
        $this->assertSame('BOOKED', $shipment->refresh()->current_status);
    }

    public function test_invalid_status_transition_returns_422_with_error_message(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $this->assertSame('CREATED', $shipment->current_status);

        Sanctum::actingAs($operator);

        // Melompat langsung dari CREATED ke DELIVERED
        $payload = [
            'status' => 'DELIVERED',
            'event_time' => now()->addDays(5)->toIso8601String(),
            'location_name' => 'Port of Singapore',
            'description' => 'Invalid jump attempt to DELIVERED status without voyage milestones.',
        ];

        $response = $this->postJson("/api/shipments/{$shipment->id}/tracking", $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('Transisi status dari CREATED ke DELIVERED tidak valid', $response->json('message'));
        $this->assertSame('CREATED', $shipment->refresh()->current_status);
    }

    public function test_validation_errors_when_required_fields_are_missing(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        Sanctum::actingAs($operator);

        $response = $this->postJson("/api/shipments/{$shipment->id}/tracking", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'event_time', 'location_name', 'description']);
    }
}
