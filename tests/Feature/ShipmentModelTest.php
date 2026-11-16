<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class ShipmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipment_seeder_populates_realistic_shipments_and_items(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);

        $this->assertDatabaseCount('shipments', 3);
        $this->assertDatabaseCount('shipment_items', 4);

        $this->assertDatabaseHas('shipments', [
            'shipment_number' => 'CT-202611-0001',
            'cargo_type' => 'Telecommunication & Electronics',
            'current_status' => 'CREATED',
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'item_name' => 'Fiber Optic Transceivers',
            'quantity' => 50,
            'hs_code' => '8517.62',
        ]);
    }

    public function test_shipment_belongs_to_customer_carrier_origin_and_destination(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class, ShipmentSeeder::class]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $this->assertInstanceOf(User::class, $shipment->customer);
        $this->assertSame('customer@cargotrace.com', $shipment->customer->email);

        $this->assertInstanceOf(Carrier::class, $shipment->carrier);
        $this->assertSame('MSK', $shipment->carrier->code);

        $this->assertInstanceOf(Location::class, $shipment->origin);
        $this->assertSame('IDTPP', $shipment->origin->code);

        $this->assertInstanceOf(Location::class, $shipment->destination);
        $this->assertSame('SGSIN', $shipment->destination->code);
    }

    public function test_shipment_has_many_items(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class, ShipmentSeeder::class]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $this->assertCount(2, $shipment->items);
        $this->assertInstanceOf(ShipmentItem::class, $shipment->items->first());
        $this->assertSame('Fiber Optic Transceivers', $shipment->items->first()->item_name);
    }

    public function test_deleting_shipment_cascades_and_deletes_shipment_items(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class, ShipmentSeeder::class]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $itemId = $shipment->items->first()->id;

        $this->assertDatabaseHas('shipment_items', ['id' => $itemId]);

        $shipment->delete();

        $this->assertDatabaseMissing('shipments', ['shipment_number' => 'CT-202611-0001']);
        $this->assertDatabaseMissing('shipment_items', ['id' => $itemId]);
    }

    public function test_query_scopes_for_customer_status_and_delayed(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class, ShipmentSeeder::class]);

        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();

        // Scope forCustomer
        $customerShipments = Shipment::forCustomer($customer->id)->get();
        $this->assertCount(3, $customerShipments);

        // Scope status
        $createdShipments = Shipment::status('CREATED')->get();
        $this->assertCount(2, $createdShipments);

        $bookedShipments = Shipment::status('BOOKED')->get();
        $this->assertCount(1, $bookedShipments);

        // Scope delayed
        $this->assertCount(0, Shipment::delayed()->get());

        // Mark one shipment as delayed
        $shipment = $createdShipments->first();
        $shipment->update([
            'is_delayed' => true,
            'delay_hours' => 12,
            'delay_severity' => 'MEDIUM',
        ]);

        $this->assertCount(1, Shipment::delayed()->get());
        $this->assertSame('CT-202611-0001', Shipment::delayed()->first()->shipment_number);
    }

    public function test_user_carrier_and_location_have_shipment_relations(): void
    {
        $this->seed([UserSeeder::class, CarrierSeeder::class, LocationSeeder::class, ShipmentSeeder::class]);

        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $this->assertCount(3, $customer->shipments);

        $carrier = Carrier::where('code', 'MSK')->firstOrFail();
        $this->assertCount(1, $carrier->shipments);

        $priok = Location::where('code', 'IDTPP')->firstOrFail();
        $this->assertCount(1, $priok->originShipments);
        $this->assertCount(1, $priok->destinationShipments);
    }
}
