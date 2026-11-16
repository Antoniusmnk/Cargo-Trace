<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_three_demo_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@cargotrace.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'operator@cargotrace.com',
            'role' => 'operator',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'customer@cargotrace.com',
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    public function test_smart_dashboard_redirects_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_smart_dashboard_redirects_operator_to_operator_dashboard(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->get('/dashboard');

        $response->assertRedirect('/operator/dashboard');
    }

    public function test_smart_dashboard_redirects_customer_to_customer_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/dashboard');

        $response->assertRedirect('/customer/dashboard');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Administrator Control Center');
    }

    public function test_admin_can_also_access_operator_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/operator/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Logistics Operations Workspace');
    }

    public function test_operator_can_access_operator_dashboard(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->get('/operator/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Logistics Operations Workspace');
    }

    public function test_operator_cannot_access_admin_dashboard(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->get('/admin/dashboard');

        $response->assertStatus(403);
        $response->assertSee('403 - Akses Dilarang');
    }

    public function test_customer_can_access_customer_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/customer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Client Shipment Portal');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertStatus(403);
        $response->assertSee('403 - Akses Dilarang');
    }

    public function test_customer_cannot_access_operator_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/operator/dashboard');

        $response->assertStatus(403);
        $response->assertSee('403 - Akses Dilarang');
    }

    public function test_unauthenticated_user_redirected_to_login_when_accessing_role_dashboards(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/operator/dashboard')->assertRedirect('/login');
        $this->get('/customer/dashboard')->assertRedirect('/login');
    }

    public function test_suspended_user_is_logged_out_and_receives_403(): void
    {
        $suspendedAdmin = User::factory()->admin()->suspended()->create();

        $response = $this->actingAs($suspendedAdmin)->get('/admin/dashboard');

        $response->assertStatus(403);
        $this->assertGuest();
    }
}
