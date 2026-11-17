<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ShipmentSeeder;
use Database\Seeders\TrackingEventSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentTrackingWebTest extends TestCase
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

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $response = $this->get("/shipments/{$shipment->id}/tracking");
        $response->assertRedirect('/login');
    }

    public function test_customer_can_view_own_shipment_timeline_web(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $response = $this->actingAs($customer)->get("/shipments/{$shipment->id}/tracking");

        $response->assertStatus(200)
            ->assertSee('CT-202611-0001')
            ->assertSee('Linimasa Perjalanan Kargo')
            ->assertSee('Carrier / Pelayaran')
            ->assertSee('Manifes Muatan Barang')
            ->assertDontSee('Catat Checkpoint Baru'); // Customer must not see add checkpoint form
    }

    public function test_customer_cannot_view_other_customer_shipment_timeline_returns_403(): void
    {
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $otherCustomer = User::factory()->create([
            'email' => 'other.client@example.com',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($otherCustomer)->get("/shipments/{$shipment->id}/tracking");
        $response->assertStatus(403);
    }

    public function test_operator_and_admin_can_view_tracking_timeline_with_action_form(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $response = $this->actingAs($operator)->get("/shipments/{$shipment->id}/tracking");

        $response->assertStatus(200)
            ->assertSee('CT-202611-0001')
            ->assertSee('Catat Checkpoint Baru')
            ->assertSee('Catat Checkpoint & Perbarui Status', false);
    }

    public function test_operator_can_add_checkpoint_via_web_form(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $this->assertSame('CREATED', $shipment->current_status);

        $payload = [
            'status' => 'BOOKED',
            'event_time' => now()->format('Y-m-d\TH:i'),
            'location_id' => $shipment->origin_id,
            'location_name' => 'Tanjung Priok Yard 2, Jakarta',
            'latitude' => -6.1018800,
            'longitude' => 106.8833800,
            'description' => 'Slot booking vessel Maersk Line confirmed via Web Operator Portal.',
        ];

        $response = $this->actingAs($operator)->post("/shipments/{$shipment->id}/tracking/checkpoints", $payload);

        $response->assertRedirect(route('shipments.tracking.show', $shipment))
            ->assertSessionHas('success', 'Checkpoint pelacakan baru berhasil dicatat dan status kargo diperbarui.');

        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'status' => 'BOOKED',
            'location_name' => 'Tanjung Priok Yard 2, Jakarta',
            'created_by' => $operator->id,
        ]);

        $this->assertSame('BOOKED', $shipment->refresh()->current_status);
    }

    public function test_invalid_status_transition_via_web_form_displays_error(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $this->assertSame('CREATED', $shipment->current_status);

        // Langsung lompat dari CREATED ke DELIVERED (invalid transition)
        $payload = [
            'status' => 'DELIVERED',
            'event_time' => now()->format('Y-m-d\TH:i'),
            'location_name' => 'Port of Singapore',
            'description' => 'Illegal direct transition attempt.',
        ];

        $response = $this->actingAs($operator)->post("/shipments/{$shipment->id}/tracking/checkpoints", $payload);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['transition_error']);

        $this->assertSame('CREATED', $shipment->refresh()->current_status);
    }

    public function test_customer_cannot_add_checkpoint_via_web_form(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $payload = [
            'status' => 'BOOKED',
            'event_time' => now()->format('Y-m-d\TH:i'),
            'location_name' => 'Jakarta Port',
            'description' => 'Customer trying to inject event',
        ];

        $response = $this->actingAs($customer)->post("/shipments/{$shipment->id}/tracking/checkpoints", $payload);

        $response->assertStatus(403);
    }
}
