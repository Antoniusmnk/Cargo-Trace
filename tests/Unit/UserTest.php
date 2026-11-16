<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_role_helper_methods_identify_admin(): void
    {
        $user = new User(['role' => 'admin', 'status' => 'active']);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isOperator());
        $this->assertFalse($user->isCustomer());
    }

    public function test_user_role_helper_methods_identify_operator(): void
    {
        $user = new User(['role' => 'operator', 'status' => 'active']);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isOperator());
        $this->assertFalse($user->isCustomer());
    }

    public function test_user_role_helper_methods_identify_customer(): void
    {
        $user = new User(['role' => 'customer', 'status' => 'active']);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isOperator());
        $this->assertTrue($user->isCustomer());
    }

    public function test_user_fillable_attributes_include_role_and_status(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'role' => 'operator',
            'status' => 'suspended',
        ]);

        $this->assertSame('operator', $user->role);
        $this->assertSame('suspended', $user->status);
    }
}
