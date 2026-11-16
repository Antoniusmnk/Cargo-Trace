<?php

namespace Tests\Feature\Api;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ShipmentSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShipmentApiCrudTest extends TestCase
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
        ]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/shipments')->assertStatus(401);
        $this->postJson('/api/shipments', [])->assertStatus(401);
        $this->getJson('/api/shipments/1')->assertStatus(401);
        $this->putJson('/api/shipments/1', [])->assertStatus(401);
        $this->deleteJson('/api/shipments/1')->assertStatus(401);
    }

    public function test_admin_and_operator_can_list_all_shipments(): void
    {
        $admin = User::where('email', 'admin@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/shipments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar pengiriman kargo berhasil diambil.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'shipment_number',
                        'customer_id',
                        'carrier_id',
                        'origin_id',
                        'destination_id',
                        'cargo_type',
                        'current_status',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'links',
            ]);

        $this->assertCount(3, $response->json('data'));

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);

        $opResponse = $this->getJson('/api/shipments');
        $opResponse->assertStatus(200);
        $this->assertCount(3, $opResponse->json('data'));
    }

    public function test_customer_can_only_list_their_own_shipments(): void
    {
        $customer1 = User::where('email', 'customer@cargotrace.com')->firstOrFail();

        $customer2 = User::factory()->create([
            'email' => 'other.client@example.com',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $carrier = Carrier::first();
        $origin = Location::first();
        $destination = Location::skip(1)->first();

        // Buat 1 shipment untuk customer 2
        Shipment::create([
            'shipment_number' => 'CT-999999-0001',
            'customer_id' => $customer2->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Automotive Parts',
            'total_weight_kg' => 1200,
            'total_volume_cbm' => 20,
            'expected_departure' => now()->addDays(1),
            'expected_arrival' => now()->addDays(7),
            'current_status' => 'CREATED',
        ]);

        // Customer 1 request
        Sanctum::actingAs($customer1);
        $res1 = $this->getJson('/api/shipments');
        $res1->assertStatus(200);

        // Seeder awal membuat 3 shipments, seluruhnya milik customer@cargotrace.com
        $this->assertCount(3, $res1->json('data'));
        foreach ($res1->json('data') as $item) {
            $this->assertEquals($customer1->id, $item['customer_id']);
        }

        // Customer 2 request
        Sanctum::actingAs($customer2);
        $res2 = $this->getJson('/api/shipments');
        $res2->assertStatus(200);
        $this->assertCount(1, $res2->json('data'));
        $this->assertEquals($customer2->id, $res2->json('data.0.customer_id'));
    }

    public function test_filter_shipments_by_status_and_search(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);

        // Filter status BOOKED
        $resStatus = $this->getJson('/api/shipments?status=BOOKED');
        $resStatus->assertStatus(200);
        $this->assertCount(1, $resStatus->json('data'));
        $this->assertEquals('CT-202611-0002', $resStatus->json('data.0.shipment_number'));

        // Search by shipment number
        $resSearch = $this->getJson('/api/shipments?search=CT-202611-0003');
        $resSearch->assertStatus(200);
        $this->assertCount(1, $resSearch->json('data'));
        $this->assertEquals('CT-202611-0003', $resSearch->json('data.0.shipment_number'));
    }

    public function test_operator_and_admin_can_create_shipment_with_items(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $carrier = Carrier::where('code', 'MSK')->firstOrFail();
        $origin = Location::where('code', 'IDTPP')->firstOrFail();
        $destination = Location::where('code', 'SGSIN')->firstOrFail();

        Sanctum::actingAs($operator);

        $payload = [
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Pharmaceutical Vaccines',
            'expected_departure' => now()->addDays(2)->toIso8601String(),
            'expected_arrival' => now()->addDays(8)->toIso8601String(),
            'estimated_arrival' => now()->addDays(9)->toIso8601String(),
            'notes' => 'Temperature sensitive cold-chain cargo',
            'items' => [
                [
                    'item_name' => 'Vaccine Vials Box A',
                    'quantity' => 100,
                    'weight_kg' => 450.50,
                    'volume_cbm' => 5.20,
                    'package_type' => 'Cold Box',
                    'hs_code' => '3002.20',
                ],
                [
                    'item_name' => 'Vaccine Vials Box B',
                    'quantity' => 150,
                    'weight_kg' => 600.00,
                    'volume_cbm' => 7.30,
                    'package_type' => 'Cold Box',
                    'hs_code' => '3002.20',
                ],
            ],
        ];

        $response = $this->postJson('/api/shipments', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pengiriman kargo berhasil dibuat.',
            ])
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.cargo_type', 'Pharmaceutical Vaccines')
            ->assertJsonPath('data.current_status', 'CREATED');

        $this->assertEquals(1050.50, (float) $response->json('data.total_weight_kg'));
        $this->assertEquals(12.50, (float) $response->json('data.total_volume_cbm'));

        $this->assertDatabaseHas('shipments', [
            'customer_id' => $customer->id,
            'cargo_type' => 'Pharmaceutical Vaccines',
            'total_weight_kg' => 1050.50,
            'total_volume_cbm' => 12.50,
            'current_status' => 'CREATED',
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'item_name' => 'Vaccine Vials Box A',
            'quantity' => 100,
        ]);
        $this->assertDatabaseHas('shipment_items', [
            'item_name' => 'Vaccine Vials Box B',
            'quantity' => 150,
        ]);
    }

    public function test_customer_cannot_create_shipment(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($customer);

        $payload = [
            'customer_id' => $customer->id,
            'carrier_id' => 1,
            'origin_id' => 1,
            'destination_id' => 2,
            'cargo_type' => 'Electronics',
            'expected_departure' => now()->addDays(1)->toDateString(),
            'expected_arrival' => now()->addDays(5)->toDateString(),
            'items' => [
                [
                    'item_name' => 'Laptop Units',
                    'quantity' => 10,
                    'weight_kg' => 25.0,
                    'volume_cbm' => 0.5,
                    'package_type' => 'Box',
                ],
            ],
        ];

        $response = $this->postJson('/api/shipments', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk membuat shipment baru.',
            ]);
    }

    public function test_create_shipment_validations(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);

        // Payload kosong
        $response = $this->postJson('/api/shipments', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id', 'carrier_id', 'origin_id', 'destination_id', 'cargo_type', 'expected_departure', 'expected_arrival', 'items']);

        // Pelabuhan asal dan tujuan sama
        $invalidOriginDest = [
            'customer_id' => 1,
            'carrier_id' => 1,
            'origin_id' => 1,
            'destination_id' => 1, // Sama dengan origin_id
            'cargo_type' => 'General Cargo',
            'expected_departure' => now()->addDays(2)->toDateString(),
            'expected_arrival' => now()->addDays(5)->toDateString(),
            'items' => [
                [
                    'item_name' => 'Box 1',
                    'quantity' => 1,
                    'weight_kg' => 10,
                    'volume_cbm' => 0.1,
                    'package_type' => 'Carton',
                ],
            ],
        ];

        $resSamePort = $this->postJson('/api/shipments', $invalidOriginDest);
        $resSamePort->assertStatus(422)
            ->assertJsonValidationErrors(['destination_id']);
    }

    public function test_authorized_user_can_view_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();

        // Customer pemilik dapat melihat
        Sanctum::actingAs($customer);
        $resCustomer = $this->getJson("/api/shipments/{$shipment->id}");
        $resCustomer->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $shipment->id,
                    'shipment_number' => 'CT-202611-0001',
                ],
            ]);

        // Operator dapat melihat
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);
        $resOp = $this->getJson("/api/shipments/{$shipment->id}");
        $resOp->assertStatus(200);
    }

    public function test_customer_cannot_view_other_customers_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $otherCustomer = User::factory()->create([
            'email' => 'intruder@client.test',
            'role' => 'customer',
            'status' => 'active',
        ]);

        Sanctum::actingAs($otherCustomer);

        $response = $this->getJson("/api/shipments/{$shipment->id}");
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk melihat pengiriman ini.',
            ]);
    }

    public function test_view_nonexistent_shipment_returns_404(): void
    {
        $admin = User::where('email', 'admin@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/shipments/999999');
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Data pengiriman kargo tidak ditemukan.',
            ]);
    }

    public function test_operator_can_update_active_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();

        Sanctum::actingAs($operator);

        $response = $this->putJson("/api/shipments/{$shipment->id}", [
            'cargo_type' => 'Updated Electronics & Microchips',
            'notes' => 'Updated via Operator PUT API',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data pengiriman kargo berhasil diperbarui.',
            ])
            ->assertJsonPath('data.cargo_type', 'Updated Electronics & Microchips')
            ->assertJsonPath('data.notes', 'Updated via Operator PUT API');

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'cargo_type' => 'Updated Electronics & Microchips',
            'notes' => 'Updated via Operator PUT API',
        ]);
    }

    public function test_customer_cannot_update_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();

        Sanctum::actingAs($customer);

        $response = $this->putJson("/api/shipments/{$shipment->id}", [
            'cargo_type' => 'Hacked Cargo Type',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_cannot_update_delivered_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0003')->firstOrFail();
        $shipment->update(['current_status' => 'DELIVERED']);

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);

        $response = $this->putJson("/api/shipments/{$shipment->id}", [
            'cargo_type' => 'Attempting modify delivered cargo',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_operator_can_delete_created_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $this->assertSame('CREATED', $shipment->current_status);

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);

        $response = $this->deleteJson("/api/shipments/{$shipment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pengiriman kargo berhasil dihapus dari sistem.',
            ]);

        $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
        $this->assertDatabaseMissing('shipment_items', ['shipment_id' => $shipment->id]);
    }

    public function test_cannot_delete_in_transit_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0002')->firstOrFail();
        $shipment->update(['current_status' => 'IN_TRANSIT']);

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        Sanctum::actingAs($operator);

        $response = $this->deleteJson("/api/shipments/{$shipment->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus pengiriman ini.',
            ]);

        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);
    }

    public function test_customer_cannot_delete_shipment(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();

        Sanctum::actingAs($customer);

        $response = $this->deleteJson("/api/shipments/{$shipment->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('shipments', ['id' => $shipment->id]);
    }

    public function test_web_routes_for_shipments_are_accessible_by_roles(): void
    {
        $admin = User::where('email', 'admin@cargotrace.com')->firstOrFail();
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();

        // Admin can access admin shipments
        $this->actingAs($admin)->get('/admin/shipments')
            ->assertStatus(200)
            ->assertSee('Daftar Pengiriman Kargo (Shipments)');

        // Operator can access operator shipments
        $this->actingAs($operator)->get('/operator/shipments')
            ->assertStatus(200)
            ->assertSee('Daftar Pengiriman Kargo (Shipments)');

        // Customer can access customer shipments
        $this->actingAs($customer)->get('/customer/shipments')
            ->assertStatus(200)
            ->assertSee('Daftar Pengiriman Kargo (Shipments)');

        // Customer is forbidden from admin shipments
        $this->actingAs($customer)->get('/admin/shipments')
            ->assertStatus(403);
    }
}
