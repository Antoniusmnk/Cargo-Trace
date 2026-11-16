<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Carbon\Carbon;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\UserSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ShipmentServicePolicyTest extends TestCase
{
    use RefreshDatabase;

    private ShipmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ShipmentService::class);
    }

    public function test_shipment_service_creates_shipment_with_items_atomically(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class]);

        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $carrier = Carrier::firstOrFail();
        $origin = Location::where('code', 'IDTPP')->firstOrFail();
        $destination = Location::where('code', 'SGSIN')->firstOrFail();

        $data = [
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Pharmaceutical Vaccines',
            'expected_departure' => Carbon::now()->addDay(),
            'expected_arrival' => Carbon::now()->addDays(3),
            'notes' => 'Temperature-controlled reefer container 2-8C.',
        ];

        $items = [
            [
                'item_name' => 'Vaccine Vials Box A',
                'quantity' => 100,
                'weight_kg' => 2500.00,
                'volume_cbm' => 8.50,
                'package_type' => 'Cold Chain Box',
                'hs_code' => '3002.20',
            ],
            [
                'item_name' => 'Diluent Solution Box B',
                'quantity' => 100,
                'weight_kg' => 1500.00,
                'volume_cbm' => 5.50,
                'package_type' => 'Cold Chain Box',
                'hs_code' => '3002.90',
            ],
        ];

        $shipment = $this->service->createShipment($data, $items);

        $this->assertNotNull($shipment->id);
        $this->assertStringStartsWith('CT-' . date('Ym') . '-', $shipment->shipment_number);
        $this->assertSame('CREATED', $shipment->current_status);
        $this->assertEquals(4000.00, (float) $shipment->total_weight_kg);
        $this->assertEquals(14.00, (float) $shipment->total_volume_cbm);
        $this->assertCount(2, $shipment->items);

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'cargo_type' => 'Pharmaceutical Vaccines',
        ]);

        $this->assertDatabaseCount('shipment_items', 2);
    }

    public function test_shipment_service_auto_generates_tracking_number(): void
    {
        $number = $this->service->generateShipmentNumber();

        $this->assertMatchesRegularExpression('/^CT-\d{6}-\d{4}$/', $number);
    }

    public function test_shipment_service_prevents_deleting_in_transit_shipment(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class]);

        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $carrier = Carrier::firstOrFail();
        $loc = Location::firstOrFail();

        $shipment = Shipment::create([
            'shipment_number' => 'CT-202611-9991',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'General Cargo',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'current_status' => 'IN_TRANSIT',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Kargo yang sedang dalam perjalanan atau telah terkirim tidak dapat dihapus.');

        $this->service->deleteShipment($shipment);
    }

    public function test_shipment_service_prevents_updating_delivered_shipment(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class]);

        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $carrier = Carrier::firstOrFail();
        $loc = Location::firstOrFail();

        $shipment = Shipment::create([
            'shipment_number' => 'CT-202611-9992',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'Heavy Machinery',
            'expected_departure' => now()->subDays(10),
            'expected_arrival' => now()->subDay(),
            'current_status' => 'DELIVERED',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Status kargo sudah DELIVERED dan tidak dapat diubah tanpa prosedur khusus.');

        $this->service->updateShipment($shipment, ['notes' => 'Attempted modification']);
    }

    public function test_shipment_service_pagination_and_role_filtering(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class]);

        $customerA = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $customerB = User::factory()->create(['email' => 'clientb@test.com', 'role' => 'customer']);
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $carrier = Carrier::firstOrFail();
        $loc = Location::firstOrFail();

        // 2 shipments for customer A, 1 for customer B
        Shipment::create([
            'shipment_number' => 'CT-202611-1001',
            'customer_id' => $customerA->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'Textiles',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'current_status' => 'CREATED',
        ]);

        Shipment::create([
            'shipment_number' => 'CT-202611-1002',
            'customer_id' => $customerA->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'Footwear',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(6),
            'current_status' => 'BOOKED',
            'is_delayed' => true,
        ]);

        Shipment::create([
            'shipment_number' => 'CT-202611-1003',
            'customer_id' => $customerB->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'Automotive Parts',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(7),
            'current_status' => 'IN_TRANSIT',
        ]);

        // Customer A only sees 2 shipments
        $paginatedA = $this->service->getPaginatedShipmentsForUser($customerA);
        $this->assertSame(2, $paginatedA->total());

        // Filter status on customer A
        $filteredStatus = $this->service->getPaginatedShipmentsForUser($customerA, ['status' => 'BOOKED']);
        $this->assertSame(1, $filteredStatus->total());

        // Filter delayed on customer A
        $filteredDelayed = $this->service->getPaginatedShipmentsForUser($customerA, ['delayed' => true]);
        $this->assertSame(1, $filteredDelayed->total());

        // Operator sees all 3 shipments
        $paginatedOperator = $this->service->getPaginatedShipmentsForUser($operator);
        $this->assertSame(3, $paginatedOperator->total());

        // Search by shipment number
        $searched = $this->service->getPaginatedShipmentsForUser($operator, ['search' => '1003']);
        $this->assertSame(1, $searched->total());
        $this->assertSame('CT-202611-1003', $searched->first()->shipment_number);
    }

    public function test_customer_cannot_view_another_customer_shipment(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class]);

        $owner = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $carrier = Carrier::firstOrFail();
        $loc = Location::firstOrFail();

        $shipment = Shipment::create([
            'shipment_number' => 'CT-202611-8888',
            'customer_id' => $owner->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'Proprietary Tech',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(3),
        ]);

        $this->assertFalse(Gate::forUser($otherCustomer)->allows('view', $shipment));
    }

    public function test_customer_can_view_own_shipment(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class]);

        $owner = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $carrier = Carrier::firstOrFail();
        $loc = Location::firstOrFail();

        $shipment = Shipment::create([
            'shipment_number' => 'CT-202611-8889',
            'customer_id' => $owner->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'Raw Materials',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(3),
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $shipment));
    }

    public function test_admin_and_operator_policy_permissions(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class]);

        $admin = User::where('email', 'admin@cargotrace.com')->firstOrFail();
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $carrier = Carrier::firstOrFail();
        $loc = Location::firstOrFail();

        $createdShipment = Shipment::create([
            'shipment_number' => 'CT-202611-7771',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'General',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(3),
            'current_status' => 'CREATED',
        ]);

        $inTransitShipment = Shipment::create([
            'shipment_number' => 'CT-202611-7772',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc->id,
            'destination_id' => $loc->id,
            'cargo_type' => 'General',
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(3),
            'current_status' => 'IN_TRANSIT',
        ]);

        // Create permission
        $this->assertTrue(Gate::forUser($admin)->allows('create', Shipment::class));
        $this->assertTrue(Gate::forUser($operator)->allows('create', Shipment::class));
        $this->assertFalse(Gate::forUser($customer)->allows('create', Shipment::class));

        // Delete permission: Operator can only delete CREATED or BOOKED
        $this->assertTrue(Gate::forUser($operator)->allows('delete', $createdShipment));
        $this->assertFalse(Gate::forUser($operator)->allows('delete', $inTransitShipment));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $inTransitShipment));
        $this->assertFalse(Gate::forUser($customer)->allows('delete', $createdShipment));
    }
}
