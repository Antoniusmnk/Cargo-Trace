<?php

namespace Tests\Unit;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\TrackingService;
use Database\Seeders\CarrierSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ShipmentSeeder;
use Database\Seeders\TrackingEventSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingStateTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected TrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trackingService = new TrackingService();
    }

    public function test_is_valid_transition_evaluates_state_machine_matrix_correctly(): void
    {
        // Valid forward transitions
        $this->assertTrue($this->trackingService->isValidTransition('CREATED', 'BOOKED'));
        $this->assertTrue($this->trackingService->isValidTransition('BOOKED', 'PICKED_UP'));
        $this->assertTrue($this->trackingService->isValidTransition('PICKED_UP', 'IN_TRANSIT'));
        $this->assertTrue($this->trackingService->isValidTransition('IN_TRANSIT', 'DELIVERED'));

        // Checkpoint update allowed during IN_TRANSIT
        $this->assertTrue($this->trackingService->isValidTransition('IN_TRANSIT', 'IN_TRANSIT'));

        // Invalid skipped transitions
        $this->assertFalse($this->trackingService->isValidTransition('CREATED', 'IN_TRANSIT'));
        $this->assertFalse($this->trackingService->isValidTransition('CREATED', 'DELIVERED'));
        $this->assertFalse($this->trackingService->isValidTransition('BOOKED', 'DELIVERED'));

        // Invalid backward transitions
        $this->assertFalse($this->trackingService->isValidTransition('BOOKED', 'CREATED'));
        $this->assertFalse($this->trackingService->isValidTransition('IN_TRANSIT', 'PICKED_UP'));

        // Terminal status: DELIVERED cannot transition anywhere
        $this->assertFalse($this->trackingService->isValidTransition('DELIVERED', 'CREATED'));
        $this->assertFalse($this->trackingService->isValidTransition('DELIVERED', 'BOOKED'));
        $this->assertFalse($this->trackingService->isValidTransition('DELIVERED', 'IN_TRANSIT'));
        $this->assertFalse($this->trackingService->isValidTransition('DELIVERED', 'DELIVERED'));
    }

    public function test_valid_status_transition_creates_tracking_event_and_updates_shipment(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $this->assertSame('CREATED', $shipment->current_status);

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();

        $event = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'BOOKED',
            'location_id' => $shipment->origin_id,
            'description' => 'Carrier MSC confirmed vessel booking slot.',
            'event_time' => now(),
        ], $operator);

        $this->assertInstanceOf(TrackingEvent::class, $event);
        $this->assertSame('BOOKED', $event->status);
        $this->assertSame($operator->id, $event->created_by);

        $shipment->refresh();
        $this->assertSame('BOOKED', $shipment->current_status);

        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'status' => 'BOOKED',
            'created_by' => $operator->id,
        ]);
    }

    public function test_invalid_status_transition_throws_exception(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();
        $this->assertSame('CREATED', $shipment->current_status);

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage('Transisi status dari CREATED ke IN_TRANSIT tidak valid.');

        // Mencoba loncat langsung dari CREATED ke IN_TRANSIT
        $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'location_name' => 'High Seas',
            'description' => 'Vessel departed directly without booking/pickup.',
        ], $operator);
    }

    public function test_terminal_delivered_status_cannot_transition_or_receive_events(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0003')->firstOrFail();
        $shipment->update(['current_status' => 'DELIVERED']);

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage('Kargo dengan status DELIVERED bersifat terminal');

        // Mencoba mengubah shipment yang sudah DELIVERED kembali ke CREATED
        $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'CREATED',
            'location_name' => 'Origin Port',
            'description' => 'Attempting to re-open delivered shipment.',
        ], $operator);
    }

    public function test_in_transit_shipment_can_receive_multiple_intermediate_checkpoints(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
        ]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0002')->firstOrFail();
        $shipment->update(['current_status' => 'IN_TRANSIT']);

        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();

        // Checkpoint 1 saat IN_TRANSIT: Selat Malaka
        $event1 = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'location_name' => 'Malacca Strait Waypoint 3',
            'latitude' => 2.1896,
            'longitude' => 102.2501,
            'description' => 'Kapal melintasi Selat Malaka dalam kondisi cuaca baik.',
            'event_time' => now()->subHours(10),
        ], $operator);

        // Checkpoint 2 saat IN_TRANSIT: Terusan Suez
        $event2 = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'location_name' => 'Suez Canal Convoy Anchorage',
            'latitude' => 29.9753,
            'longitude' => 32.5599,
            'description' => 'Kapal memasuki antrean konvoi selatan Terusan Suez.',
            'event_time' => now()->subHours(2),
        ], $operator);

        $this->assertSame('IN_TRANSIT', $shipment->refresh()->current_status);
        $this->assertDatabaseHas('tracking_events', ['id' => $event1->id, 'status' => 'IN_TRANSIT']);
        $this->assertDatabaseHas('tracking_events', ['id' => $event2->id, 'status' => 'IN_TRANSIT']);
    }

    public function test_get_shipment_timeline_returns_events_in_chronological_order(): void
    {
        $this->seed([
            UserSeeder::class,
            CarrierSeeder::class,
            LocationSeeder::class,
            ShipmentSeeder::class,
            TrackingEventSeeder::class,
        ]);

        $shipment = Shipment::where('shipment_number', 'CT-202611-0002')->firstOrFail();
        $timeline = $this->trackingService->getShipmentTimeline($shipment);

        $this->assertCount(2, $timeline);
        $this->assertSame('CREATED', $timeline->first()->status);
        $this->assertSame('BOOKED', $timeline->last()->status);

        // Verifikasi urutan event_time ascending
        $this->assertTrue(
            $timeline->first()->event_time->lte($timeline->last()->event_time)
        );
    }
}
