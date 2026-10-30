<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole('admin');
        $this->teacher = User::factory()->create()->assignRole('teacher');
    }

    public function test_users_without_the_permission_are_forbidden(): void
    {
        $this->actingAs($this->teacher)->get('/users')->assertForbidden();
        $this->actingAs($this->teacher)->post('/users', [])->assertForbidden();
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        $this->actingAs($this->admin)->post('/users', [
            'name' => 'New Teacher',
            'email' => 'new.teacher@example.com',
            'password' => 'a-strong-password-123',
            'role' => 'teacher',
        ])->assertRedirect('/users');

        $user = User::where('email', 'new.teacher@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('teacher'));
    }

    public function test_created_user_emails_must_be_unique(): void
    {
        $this->actingAs($this->admin)->post('/users', [
            'name' => 'Duplicate',
            'email' => $this->teacher->email,
            'password' => 'a-strong-password-123',
            'role' => 'teacher',
        ])->assertInvalid(['email']);
    }

    public function test_admin_can_change_another_users_role(): void
    {
        $this->actingAs($this->admin)->put("/users/{$this->teacher->id}", [
            'role' => 'guardian',
        ])->assertRedirect('/users');

        $this->assertTrue($this->teacher->fresh()->hasRole('guardian'));
        $this->assertFalse($this->teacher->fresh()->hasRole('teacher'));
    }

    public function test_admin_cannot_remove_their_own_admin_role(): void
    {
        $this->actingAs($this->admin)->put("/users/{$this->admin->id}", [
            'role' => 'teacher',
        ])->assertSessionHasErrors('role');

        $this->assertTrue($this->admin->fresh()->hasRole('admin'));
    }

    public function test_admin_can_delete_another_user(): void
    {
        $this->actingAs($this->admin)->delete("/users/{$this->teacher->id}")
            ->assertRedirect('/users');

        $this->assertDatabaseMissing('users', ['id' => $this->teacher->id]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $this->actingAs($this->admin)->delete("/users/{$this->admin->id}")
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
