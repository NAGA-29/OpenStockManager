<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    // =========================================================================
    // Role Constants
    // =========================================================================

    public function test_role_admin_constant_value(): void
    {
        $this->assertEquals('admin', User::ROLE_ADMIN);
    }

    public function test_role_user_constant_value(): void
    {
        $this->assertEquals('user', User::ROLE_USER);
    }

    // =========================================================================
    // isAdmin
    // =========================================================================

    public function test_isAdmin_returns_true_for_admin_role(): void
    {
        $user = new User();
        $user->role = User::ROLE_ADMIN;

        $this->assertTrue($user->isAdmin());
    }

    public function test_isAdmin_returns_false_for_user_role(): void
    {
        $user = new User();
        $user->role = User::ROLE_USER;

        $this->assertFalse($user->isAdmin());
    }

    public function test_isAdmin_returns_false_for_null_role(): void
    {
        $user = new User();
        $user->role = null;

        $this->assertFalse($user->isAdmin());
    }

    public function test_isAdmin_returns_false_for_arbitrary_string(): void
    {
        $user = new User();
        $user->role = 'manager';

        $this->assertFalse($user->isAdmin());
    }

    public function test_isAdmin_is_case_sensitive(): void
    {
        $user = new User();
        $user->role = 'Admin';

        $this->assertFalse($user->isAdmin());
    }

    // =========================================================================
    // Fillable
    // =========================================================================

    public function test_fillable_contains_expected_fields(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
    }

    // =========================================================================
    // Hidden
    // =========================================================================

    public function test_hidden_contains_sensitive_fields(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    // =========================================================================
    // Casts
    // =========================================================================

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }
}
