<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // Roles must exist for registration, so run the full DatabaseSeeder.
    protected bool $seed = true;

    public function test_visitors_can_register_as_guardian_or_student(): void
    {
        foreach ([Roles::GUARDIAN, Roles::STUDENT] as $role) {
            $email = $role.'-register-test@example.com';

            $response = $this->post('/register', [
                'name' => 'Demo '.$role,
                'email' => $email,
                'password' => 'Password1234',
                'password_confirmation' => 'Password1234',
                'role' => $role,
            ]);

            $user = User::where('email', $email)->first();

            $this->assertNotNull($user);
            $this->assertTrue($user->hasRole($role));
            $this->assertAuthenticatedAs($user);

            $response->assertRedirect(route('dashboard', absolute: false));

            $this->post('/logout');
        }
    }

    public function test_registration_screen_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create Account');
    }

    public function test_visitors_cannot_register_as_admin_or_teacher(): void
    {
        foreach ([Roles::ADMIN, Roles::TEACHER] as $role) {
            $this->from('/register')->post('/register', [
                'name' => 'Impostor',
                'email' => $role.'-register-test@example.com',
                'password' => 'Password1234',
                'password_confirmation' => 'Password1234',
                'role' => $role,
            ]);

            $this->assertNull(User::where('email', $role.'-register-test@example.com')->first());
            $this->assertGuest();
        }
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'Password1234',
            'password_confirmation' => 'Password1234',
            'role' => Roles::GUARDIAN,
        ])->assertInvalid(['email']);
    }
}
