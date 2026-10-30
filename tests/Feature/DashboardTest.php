<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_sees_student_and_guardian_counts(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Student::factory()->count(3)->create();
        Guardian::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('students.index'));
        $response->assertSee(route('guardians.index'));
        $response->assertSee(route('setup.index'));
        $this->assertSame(3, collect($response->viewData('cards'))->firstWhere('label', 'Students')['value']);
    }

    public function test_guardian_sees_only_the_portal_card(): void
    {
        $guardianUser = User::factory()->create()->assignRole('guardian');
        Guardian::factory()->create(['user_id' => $guardianUser->id]);

        $response = $this->actingAs($guardianUser)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('portal.index'));
        $response->assertDontSee(route('students.index'));
        $response->assertDontSee(route('setup.index'));
    }

    public function test_teacher_sees_their_marks_card(): void
    {
        $teacher = User::factory()->create()->assignRole('teacher');

        $response = $this->actingAs($teacher)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('marks.index'));
        $response->assertDontSee(route('guardians.index'));
    }
}
