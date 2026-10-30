<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_screen_renders_for_guests(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Log in');
    }

    public function test_guests_are_redirected_to_login_from_protected_pages(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_cannot_authenticate_with_an_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }
}
