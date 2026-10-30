<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_reset_link_request_screen_renders(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot Password');
    }

    public function test_reset_link_is_sent_to_existing_users(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_emails_get_no_leaking_error(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

        $response->assertSessionHasNoErrors();
        Notification::assertNothingSent();
    }

    public function test_the_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('login', absolute: false));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'NewPassword123',
        ]);

        $this->assertAuthenticatedAs($user);
    }
}
