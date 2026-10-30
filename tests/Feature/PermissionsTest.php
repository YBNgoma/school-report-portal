<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Start every test from a clean permission cache.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_has_every_permission(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        foreach (Permissions::ALL as $permission) {
            $this->assertTrue($admin->can($permission), "admin should hold {$permission}");
        }
    }

    public function test_teacher_can_enter_marks_but_cannot_manage_users(): void
    {
        $teacher = User::factory()->create()->assignRole('teacher');

        $this->assertTrue($teacher->can(Permissions::MARKS_ENTER));
        $this->assertTrue($teacher->can(Permissions::REPORTS_COMPILE));
        $this->assertFalse($teacher->can(Permissions::USERS_MANAGE));
        $this->assertFalse($teacher->can(Permissions::SETUP_MANAGE));
    }

    public function test_guardian_and_student_only_have_portal_access(): void
    {
        foreach (['guardian', 'student'] as $role) {
            $user = User::factory()->create()->assignRole($role);

            $this->assertTrue($user->can(Permissions::PORTAL_REPORTS_VIEW));
            $this->assertFalse($user->can(Permissions::MARKS_VIEW));
            $this->assertFalse($user->can(Permissions::USERS_MANAGE));
        }
    }

    public function test_role_middleware_blocks_guardians_from_admin_pages(): void
    {
        $guardian = User::factory()->create()->assignRole('guardian');
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($guardian)->get('/admin-only')->assertForbidden();
        $this->actingAs($admin)->get('/admin-only')->assertOk();
    }

    public function test_permission_middleware_gates_user_management(): void
    {
        $teacher = User::factory()->create()->assignRole('teacher');
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($teacher)->get('/manage-users')->assertForbidden();
        $this->actingAs($admin)->get('/manage-users')->assertOk();
    }

    public function test_users_without_roles_have_no_portal_permissions(): void
    {
        $user = User::factory()->create();

        foreach (Permissions::ALL as $permission) {
            $this->assertFalse($user->can($permission), "{$permission} should be denied");
        }
    }
}
