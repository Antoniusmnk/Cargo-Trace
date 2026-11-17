<?php

namespace Tests\Feature;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShipmentLifecycleEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private ShipmentService $shipmentService;
    private TrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shipmentService = app(ShipmentService::class);
        $this->trackingService = app(TrackingService::class);
    }

    /**
     * Helper to create baseline entities.
     *
     * @return array{operator: User, customer: User, carrier: Carrier, origin: Location, destination: Location}
     */
    private function setupLifecycleEntities(): array
    {
        $operator = User::factory()->create([
            'name' => 'Logistics Operations Officer',
            'email' => 'operator.e2e@cargotrace.com',
            'role' => 'operator',
            'status' => 'active',
        ]);

        $customer = User::factory()->create([
            'name' => 'Global Importer Client',
            'email' => 'customer.e2e@cargotrace.com',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $carrier = Carrier::create([
            'code' => 'MSK',
            'name' => 'Maersk Line',
            'contact_email' => 'ops@maersk.test',
            'status' => 'active',
        ]);

        $origin = Location::create([
            'code' => 'IDTPP',
            'name' => 'Port of Tanjung Priok',
            'type' => 'port',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.1018800,
            'longitude' => 106.8833800,
        ]);

        $destination = Location::create([
            'code' => 'SGSIN',
            'name' => 'Port of Singapore',
            'type' => 'port',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.2644000,
            'longitude' => 103.8222000,
        ]);

        return compact('operator', 'customer', 'carrier', 'origin', 'destination');
    }

    public function test_full_shipment_lifecycle_from_created_to_delivered_success(): void
    {
        extract($this->setupLifecycleEntities());

        $now = Carbon::now();

        // 1. Buat Shipment via ShipmentService
        $shipmentData = [
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Medical Equipment & Precision Sensors',
            'expected_departure' => (clone $now)->addDays(1),
            'expected_arrival' => (clone $now)->addDays(5),
            'notes' => 'High-value medical devices consignment.',
        ];

        $items = [
            [
                'item_name' => 'Digital X-Ray Sensors',
                'quantity' => 40,
                'weight_kg' => 1200.00,
                'volume_cbm' => 6.50,
                'package_type' => 'Reinforced Wooden Crate',
                'hs_code' => '9022.90',
            ],
            [
                'item_name' => 'Ultrasonic Transducers',
                'quantity' => 80,
                'weight_kg' => 800.00,
                'volume_cbm' => 4.20,
                'package_type' => 'Shockproof Carton',
                'hs_code' => '9018.19',
            ],
        ];

        $shipment = $this->shipmentService->createShipment($shipmentData, $items);

        $this->assertSame('CREATED', $shipment->current_status);
        $this->assertEquals(2000.00, (float) $shipment->total_weight_kg);
        $this->assertEquals(10.70, (float) $shipment->total_volume_cbm);
        $this->assertDatabaseCount('tracking_events', 0);

        // 2. Operator menambahkan checkpoint 'BOOKED'
        $eventBooked = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'BOOKED',
            'location_id' => $origin->id,
            'location_name' => 'Jakarta Commercial Booking Office',
            'description' => 'Carrier Maersk Line mengonfirmasi slot kontainer 20ft untuk pelayaran.',
            'event_time' => (clone $now)->subDays(4),
        ], $operator);

        $this->assertSame('BOOKED', $shipment->refresh()->current_status);
        $this->assertDatabaseCount('tracking_events', 1);
        $this->assertSame('BOOKED', $eventBooked->status);

        // 3. Operator menambahkan checkpoint 'PICKED_UP'
        $eventPickedUp = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'PICKED_UP',
            'location_id' => $origin->id,
            'location_name' => 'Depot Kontainer Cakung, Jakarta Timur',
            'description' => 'Kontainer kosong telah diambil dari depo dan selesai dimuat oleh tim ekspedisi.',
            'event_time' => (clone $now)->subDays(3),
        ], $operator);

        $this->assertSame('PICKED_UP', $shipment->refresh()->current_status);
        $this->assertDatabaseCount('tracking_events', 2);
        $this->assertSame('PICKED_UP', $eventPickedUp->status);

        // 4. Operator menambahkan checkpoint 'IN_TRANSIT' (di Pelabuhan Tanjung Priok)
        $eventInTransit1 = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'location_id' => $origin->id,
            'location_name' => 'Pelabuhan Tanjung Priok Berth 102, Jakarta',
            'latitude' => -6.1018800,
            'longitude' => 106.8833800,
            'description' => 'Kontainer telah dimuat ke kapal MV Maersk Sentosa dan kapal telah bertolak (ATD tercatat).',
            'event_time' => (clone $now)->subDays(2),
        ], $operator);

        $this->assertSame('IN_TRANSIT', $shipment->refresh()->current_status);
        $this->assertDatabaseCount('tracking_events', 3);
        $this->assertSame('IN_TRANSIT', $eventInTransit1->status);

        // 5. Operator menambahkan checkpoint update 'IN_TRANSIT' kedua (sedang berlayar di Selat Karimata)
        $eventInTransit2 = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'location_name' => 'Selat Karimata Maritime Corridor',
            'latitude' => -1.8250000,
            'longitude' => 108.8500000,
            'description' => 'Posisi kapal termonitor di Selat Karimata. Kecepatan 16 knots, navigasi normal tanpa deviasi.',
            'event_time' => (clone $now)->subDay(),
        ], $operator);

        $this->assertSame('IN_TRANSIT', $shipment->refresh()->current_status);
        $this->assertDatabaseCount('tracking_events', 4);
        $this->assertSame('IN_TRANSIT', $eventInTransit2->status);

        // 6. Operator menambahkan checkpoint 'DELIVERED' (di Port of Singapore)
        $eventDelivered = $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'DELIVERED',
            'location_id' => $destination->id,
            'location_name' => 'Port of Singapore Tanjong Pagar Terminal',
            'latitude' => 1.2644000,
            'longitude' => 103.8222000,
            'description' => 'Kapal sandar dengan selamat di dermaga tujuan. Kontainer telah dibongkar dan diserahterimakan kepada consignee.',
            'event_time' => $now,
        ], $operator);

        $this->assertSame('DELIVERED', $shipment->refresh()->current_status);
        $this->assertDatabaseCount('tracking_events', 5);
        $this->assertSame('DELIVERED', $eventDelivered->status);

        // 7. Verifikasi Immutability & Kronologi Timeline
        $timeline = $this->trackingService->getShipmentTimeline($shipment);

        $this->assertCount(5, $timeline);
        $statuses = $timeline->pluck('status')->toArray();
        $this->assertSame(['BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'IN_TRANSIT', 'DELIVERED'], $statuses);

        // Pastikan terurut kronologis secara menaik (ascending)
        for ($i = 0; $i < $timeline->count() - 1; $i++) {
            $this->assertTrue(
                $timeline[$i]->event_time->lte($timeline[$i + 1]->event_time),
                "Event index {$i} harus sebelum atau sama dengan event index " . ($i + 1)
            );
        }

        // Pastikan seluruh event dicatat oleh operator yang sah
        foreach ($timeline as $ev) {
            $this->assertEquals($operator->id, $ev->created_by);
            $this->assertEquals($shipment->id, $ev->shipment_id);
        }
    }

    public function test_cannot_skip_lifecycle_steps(): void
    {
        extract($this->setupLifecycleEntities());

        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Textiles',
            'expected_departure' => now()->addDay(),
            'expected_arrival' => now()->addDays(4),
        ]);

        $this->assertSame('CREATED', $shipment->current_status);

        // Coba melompat langsung dari CREATED ke IN_TRANSIT
        try {
            $this->trackingService->addTrackingEvent($shipment, [
                'status' => 'IN_TRANSIT',
                'location_name' => 'Origin Port',
                'description' => 'Illegal jump attempt',
                'event_time' => now(),
            ], $operator);
            $this->fail('Harus melempar InvalidStatusTransitionException saat melompat status.');
        } catch (InvalidStatusTransitionException $e) {
            $this->assertStringContainsString('Transisi status dari CREATED ke IN_TRANSIT tidak valid', $e->getMessage());
        }

        // Coba melompat langsung dari CREATED ke DELIVERED
        try {
            $this->trackingService->addTrackingEvent($shipment, [
                'status' => 'DELIVERED',
                'location_name' => 'Destination Port',
                'description' => 'Illegal direct delivery attempt',
                'event_time' => now(),
            ], $operator);
            $this->fail('Harus melempar InvalidStatusTransitionException saat melompat status ke DELIVERED.');
        } catch (InvalidStatusTransitionException $e) {
            $this->assertStringContainsString('Transisi status dari CREATED ke DELIVERED tidak valid', $e->getMessage());
        }

        $this->assertSame('CREATED', $shipment->refresh()->current_status);
        $this->assertDatabaseCount('tracking_events', 0);
    }

    public function test_terminal_status_delivered_cannot_transition_further(): void
    {
        extract($this->setupLifecycleEntities());

        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Industrial Machinery',
            'expected_departure' => now()->subDays(5),
            'expected_arrival' => now()->subDay(),
        ]);

        // Kargo bertransisi hingga DELIVERED
        $shipment->update(['current_status' => 'DELIVERED']);

        // 1. Coba transisi kembali ke IN_TRANSIT
        try {
            $this->trackingService->addTrackingEvent($shipment, [
                'status' => 'IN_TRANSIT',
                'location_name' => 'High Seas',
                'description' => 'Attempting rollback from DELIVERED',
                'event_time' => now(),
            ], $operator);
            $this->fail('Harus melempar InvalidStatusTransitionException saat transisi dari DELIVERED.');
        } catch (InvalidStatusTransitionException $e) {
            $this->assertStringContainsString('Kargo dengan status DELIVERED bersifat terminal', $e->getMessage());
        }

        // 2. Coba transisi kembali ke CREATED
        try {
            $this->trackingService->addTrackingEvent($shipment, [
                'status' => 'CREATED',
                'location_name' => 'Origin Port',
                'description' => 'Attempting reset to CREATED',
                'event_time' => now(),
            ], $operator);
            $this->fail('Harus melempar InvalidStatusTransitionException saat transisi dari DELIVERED ke CREATED.');
        } catch (InvalidStatusTransitionException $e) {
            $this->assertStringContainsString('Kargo dengan status DELIVERED bersifat terminal', $e->getMessage());
        }

        // 3. Coba transisi ke status yang sama (DELIVERED -> DELIVERED)
        try {
            $this->trackingService->addTrackingEvent($shipment, [
                'status' => 'DELIVERED',
                'location_name' => 'Port Yard',
                'description' => 'Attempting duplicate DELIVERED checkpoint',
                'event_time' => now(),
            ], $operator);
            $this->fail('Harus melempar InvalidStatusTransitionException pada status terminal DELIVERED.');
        } catch (InvalidStatusTransitionException $e) {
            $this->assertStringContainsString('Kargo dengan status DELIVERED bersifat terminal', $e->getMessage());
        }

        $this->assertSame('DELIVERED', $shipment->refresh()->current_status);
    }

    public function test_customer_can_retrieve_full_lifecycle_timeline_via_api(): void
    {
        extract($this->setupLifecycleEntities());

        $now = Carbon::now();

        $shipment = $this->shipmentService->createShipment([
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $destination->id,
            'cargo_type' => 'Consumer Electronics Consignment',
            'expected_departure' => (clone $now)->subDays(3),
            'expected_arrival' => $now,
        ]);

        // Simulasikan 5 event lifecycle lengkap
        $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'BOOKED',
            'location_id' => $origin->id,
            'location_name' => 'Tanjung Priok Office',
            'description' => 'Booking slot confirmed',
            'event_time' => (clone $now)->subDays(4),
        ], $operator);

        $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'PICKED_UP',
            'location_id' => $origin->id,
            'location_name' => 'Tanjung Priok Container Yard',
            'description' => 'Cargo picked up and stuffed',
            'event_time' => (clone $now)->subDays(3),
        ], $operator);

        $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'location_id' => $origin->id,
            'location_name' => 'Port of Tanjung Priok Berth 1',
            'description' => 'Vessel departed',
            'event_time' => (clone $now)->subDays(2),
        ], $operator);

        $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'IN_TRANSIT',
            'location_name' => 'Sunda Strait Checkpoint',
            'description' => 'Vessel waypoint crossing',
            'event_time' => (clone $now)->subDay(),
        ], $operator);

        $this->trackingService->addTrackingEvent($shipment, [
            'status' => 'DELIVERED',
            'location_id' => $destination->id,
            'location_name' => 'Port of Singapore',
            'description' => 'Discharged and cargo delivered to client',
            'event_time' => $now,
        ], $operator);

        $this->assertSame('DELIVERED', $shipment->refresh()->current_status);

        // Authenticate as Customer pemilik kargo via Sanctum
        Sanctum::actingAs($customer);

        $response = $this->getJson("/api/shipments/{$shipment->id}/tracking");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Riwayat perjalanan kargo berhasil diambil.',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'shipment_number' => $shipment->shipment_number,
                    'current_status' => 'DELIVERED',
                    'carrier' => 'Maersk Line',
                    'origin' => 'Port of Tanjung Priok',
                    'destination' => 'Port of Singapore',
                ],
            ]);

        $timeline = $response->json('data.timeline');
        $this->assertCount(5, $timeline);

        $this->assertSame('BOOKED', $timeline[0]['status']);
        $this->assertSame('PICKED_UP', $timeline[1]['status']);
        $this->assertSame('IN_TRANSIT', $timeline[2]['status']);
        $this->assertSame('IN_TRANSIT', $timeline[3]['status']);
        $this->assertSame('DELIVERED', $timeline[4]['status']);
    }
}
