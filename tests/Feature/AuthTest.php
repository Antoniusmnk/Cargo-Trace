<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CargoTrace');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Platform');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Kargo');
    }

    public function test_new_users_can_register_as_customer_with_active_status(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@cargotrace.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'budi@cargotrace.test',
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@cargotrace.test',
            'password' => 'password123',
            'role' => 'operator',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'operator@cargotrace.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@cargotrace.test',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'customer@cargotrace.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_suspended_users_cannot_authenticate(): void
    {
        $user = User::factory()->suspended()->create([
            'email' => 'suspended@cargotrace.test',
            'password' => 'password123',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'suspended@cargotrace.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_unauthenticated_users_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_dashboard_with_their_role(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Utama',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Utama');
        $response->assertSee('ADMINISTRATOR UTAMA');
    }

    public function test_authenticated_users_are_redirected_from_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }
}
