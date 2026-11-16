<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_active_carriers(): void
    {
        $this->seed(CarrierSeeder::class);
        $user = User::factory()->create();

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/carriers');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar carrier berhasil diambil.',
            ])
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment(['code' => 'MSK', 'name' => 'Maersk Line'])
            ->assertJsonFragment(['code' => 'MSC', 'name' => 'Mediterranean Shipping Company']);
    }

    public function test_unauthenticated_request_to_master_data_is_rejected(): void
    {
        $this->getJson('/api/carriers')->assertStatus(401);
        $this->getJson('/api/locations')->assertStatus(401);
    }

    public function test_admin_and_operator_can_create_carrier(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin_token')->plainTextToken;

        $payload = [
            'code' => 'EMC',
            'name' => 'Evergreen Marine Corp',
            'contact_email' => 'service@evergreen-marine.com',
            'phone' => '+886 2 2501 1111',
            'status' => 'active',
        ];

        $response = $this->withToken($token)->postJson('/api/carriers', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Carrier berhasil ditambahkan.',
                'data' => [
                    'code' => 'EMC',
                    'name' => 'Evergreen Marine Corp',
                ],
            ]);

        $this->assertDatabaseHas('carriers', [
            'code' => 'EMC',
            'name' => 'Evergreen Marine Corp',
        ]);
    }

    public function test_customer_cannot_create_carrier(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('cust_token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/carriers', [
            'code' => 'HLC',
            'name' => 'Hapag-Lloyd',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengelola carrier.',
            ]);
    }

    public function test_carrier_validation_prevents_duplicate_code(): void
    {
        Carrier::create([
            'code' => 'ONE',
            'name' => 'Ocean Network Express',
        ]);

        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/carriers', [
            'code' => 'ONE',
            'name' => 'Another ONE',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_authenticated_user_can_get_locations(): void
    {
        $this->seed(LocationSeeder::class);
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/locations');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar lokasi logistik berhasil diambil.',
            ])
            ->assertJsonCount(6, 'data')
            ->assertJsonFragment(['code' => 'IDTPP', 'city' => 'Jakarta'])
            ->assertJsonFragment(['code' => 'NLRTM', 'city' => 'Rotterdam']);
    }

    public function test_admin_and_operator_can_create_location(): void
    {
        $operator = User::factory()->operator()->create();
        $token = $operator->createToken('operator_token')->plainTextToken;

        $payload = [
            'code' => 'JPTYO',
            'name' => 'Port of Tokyo',
            'type' => 'port',
            'address' => 'Aomi Container Terminal, Koto City',
            'city' => 'Tokyo',
            'country' => 'Japan',
            'latitude' => 35.6190000,
            'longitude' => 139.7900000,
        ];

        $response = $this->withToken($token)->postJson('/api/locations', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Lokasi berhasil ditambahkan.',
                'data' => [
                    'code' => 'JPTYO',
                    'name' => 'Port of Tokyo',
                    'city' => 'Tokyo',
                ],
            ]);

        $this->assertDatabaseHas('locations', [
            'code' => 'JPTYO',
            'country' => 'Japan',
        ]);
    }

    public function test_customer_cannot_create_location(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('cust_token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/locations', [
            'code' => 'MYPKG',
            'name' => 'Port Klang',
            'city' => 'Klang',
            'country' => 'Malaysia',
            'latitude' => 3.0000,
            'longitude' => 101.4000,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengelola lokasi.',
            ]);
    }

    public function test_location_validation_validates_required_fields_and_coordinates(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin_token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/locations', [
            'code' => 'INVALID',
            'name' => 'Invalid Coordinates Location',
            'city' => 'Unknown',
            'country' => 'Nowhere',
            'latitude' => 999.00, // Invalid latitude
            'longitude' => 999.00, // Invalid longitude
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_web_routes_for_master_data_accessible_by_admin_and_operator(): void
    {
        $this->seed([CarrierSeeder::class, LocationSeeder::class]);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/carriers')->assertStatus(200)->assertSee('Maersk Line');
        $this->actingAs($admin)->get('/admin/locations')->assertStatus(200)->assertSee('Port of Tanjung Priok');

        $operator = User::factory()->operator()->create();
        $this->actingAs($operator)->get('/operator/carriers')->assertStatus(200)->assertSee('Maersk Line');
        $this->actingAs($operator)->get('/operator/locations')->assertStatus(200)->assertSee('Port of Tanjung Priok');

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/carriers')->assertStatus(403);
        $this->actingAs($customer)->get('/operator/carriers')->assertStatus(403);
    }
}
