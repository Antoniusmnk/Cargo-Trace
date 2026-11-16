<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api(): void
    {
        $payload = [
            'name' => 'PT Samudera Logistik',
            'email' => 'client@samudera.test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registrasi akun berhasil.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'client@samudera.test',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_can_login_via_api_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'operator.api@cargotrace.test',
            'password' => 'password123',
            'role' => 'operator',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'operator.api@cargotrace.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login API berhasil.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'role' => 'operator',
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user.auth@cargotrace.test',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user.auth@cargotrace.test',
            'password' => 'wrong-secret',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Kredensial email atau password salah.',
            ]);
    }

    public function test_suspended_user_cannot_login_via_api(): void
    {
        User::factory()->suspended()->create([
            'email' => 'suspended.api@cargotrace.test',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'suspended.api@cargotrace.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan.',
            ]);
    }

    public function test_unauthenticated_request_to_user_endpoint_is_rejected(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_profile_and_logout(): void
    {
        $user = User::factory()->create([
            'name' => 'Capt. Baharuddin',
            'email' => 'baharuddin@maritim.test',
            'role' => 'operator',
            'status' => 'active',
        ]);

        $token = $user->createToken('cargo_trace_api')->plainTextToken;

        // Fetch User Profile
        $profileResponse = $this->withToken($token)->getJson('/api/user');

        $profileResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'Capt. Baharuddin',
                    'email' => 'baharuddin@maritim.test',
                    'role' => 'operator',
                    'status' => 'active',
                ],
            ]);

        // Logout & Revoke Token
        $logoutResponse = $this->withToken($token)->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token berhasil dicabut (Logout sukses).',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_registration_validation_fails_with_422(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}
