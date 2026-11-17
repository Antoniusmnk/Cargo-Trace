<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class ShipmentExceptionWebTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_exception_center_redirects_to_login(): void
    {
        $response = $this->get('/exceptions');
        $response->assertRedirect('/login');
    }

    public function test_operator_can_view_exception_center_web(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();

        $response = $this->actingAs($operator)->get('/exceptions');

        $response->assertStatus(200)
            ->assertSee('Disruption & Exception Control Center', false)
            ->assertSee('Total Kasus')
            ->assertSee('Kendala Aktif')
            ->assertSee('Terselesaikan')
            ->assertSee('CUSTOMS_HOLD');
    }

    public function test_admin_can_view_exception_center_web(): void
    {
        $admin = User::where('email', 'admin@cargotrace.com')->firstOrFail();

        $response = $this->actingAs($admin)->get('/exceptions');

        $response->assertStatus(200)
            ->assertSee('Disruption & Exception Control Center', false);
    }

    public function test_customer_cannot_view_exception_center_web_returns_403(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();

        $response = $this->actingAs($customer)->get('/exceptions');

        $response->assertStatus(403);
    }

    public function test_operator_can_report_exception_via_web_form_and_redirects(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $payload = [
            'exception_type' => 'PORT_CONGESTION',
            'severity' => 'MEDIUM',
            'impact_hours' => 14,
            'description' => 'Antrean dermaga Tanjung Priok melebihi kapasitas standar operasional.',
        ];

        $response = $this->actingAs($operator)
            ->from("/shipments/{$shipment->id}/tracking")
            ->post(route('shipments.exceptions.store', $shipment), $payload);

        $response->assertRedirect("/shipments/{$shipment->id}/tracking")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('shipment_exceptions', [
            'shipment_id' => $shipment->id,
            'reported_by' => $operator->id,
            'exception_type' => 'PORT_CONGESTION',
            'severity' => 'MEDIUM',
            'status' => 'OPEN',
            'impact_hours' => 14,
        ]);

        $shipment->refresh();
        $this->assertTrue((bool) $shipment->is_delayed);
        $this->assertGreaterThanOrEqual(14, $shipment->delay_hours);
    }

    public function test_customer_cannot_report_exception_returns_403(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $payload = [
            'exception_type' => 'WEATHER_DELAY',
            'severity' => 'HIGH',
            'impact_hours' => 48,
            'description' => 'Upaya ilegal customer melaporkan kendala.',
        ];

        $response = $this->actingAs($customer)
            ->post(route('shipments.exceptions.store', $shipment), $payload);

        $response->assertStatus(403);
    }

    public function test_operator_can_resolve_exception_via_web_modal(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        $exception = ShipmentException::create([
            'shipment_id' => $shipment->id,
            'reported_by' => $operator->id,
            'exception_type' => 'CUSTOMS_HOLD',
            'status' => 'OPEN',
            'severity' => 'HIGH',
            'impact_hours' => 24,
            'description' => 'Pemeriksaan fisik peti kemas di terminal.',
            'detected_at' => now()->subDay(),
            'resolved_at' => null,
            'resolution_notes' => null,
        ]);

        $payload = [
            'resolution_notes' => 'Dokumen PEB telah divalidasi dan bea cukai telah menerbitkan SPPB.',
        ];

        $response = $this->actingAs($operator)
            ->from('/exceptions')
            ->post(route('exceptions.resolve', $exception), $payload);

        $response->assertRedirect('/exceptions')
            ->assertSessionHas('success');

        $exception->refresh();
        $this->assertSame('RESOLVED', $exception->status);
        $this->assertNotNull($exception->resolved_at);
        $this->assertSame('Dokumen PEB telah divalidasi dan bea cukai telah menerbitkan SPPB.', $exception->resolution_notes);
    }

    public function test_customer_cannot_resolve_exception_returns_403(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $exception = ShipmentException::where('status', 'OPEN')->firstOrFail();

        $response = $this->actingAs($customer)
            ->post(route('exceptions.resolve', $exception), [
                'resolution_notes' => 'Upaya customer menutup kendala.',
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_view_exceptions_and_delay_banner_on_own_shipment_timeline(): void
    {
        $customer = User::where('email', 'customer@cargotrace.com')->firstOrFail();
        $shipment = Shipment::where('shipment_number', 'CT-202611-0001')->firstOrFail();

        // Pastikan kargo terhubung dengan customer dan berstatus delay
        $shipment->update([
            'customer_id' => $customer->id,
            'is_delayed' => true,
            'delay_hours' => 28,
            'delay_severity' => 'HIGH',
            'estimated_arrival' => now()->addDays(5),
        ]);

        $response = $this->actingAs($customer)->get("/shipments/{$shipment->id}/tracking");

        $response->assertStatus(200)
            ->assertSee('Peringatan Keterlambatan')
            ->assertSee('Riwayat Kendala & Gangguan', false)
            ->assertDontSee('Laporkan Kendala Baru') // Customer tidak melihat tombol aksi operator
            ->assertDontSee('Selesaikan Kendala');
    }

    public function test_shipments_list_displays_delay_badges_correctly(): void
    {
        $operator = User::where('email', 'operator@cargotrace.com')->firstOrFail();

        $response = $this->actingAs($operator)->get('/operator/shipments');

        $response->assertStatus(200)
            ->assertSee('Delayed (+')
            ->assertSee('On Schedule')
            ->assertSee('Exception Center');
    }
}