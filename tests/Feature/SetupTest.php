<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole('admin');
        $this->teacher = User::factory()->create()->assignRole('teacher');
        $this->guardian = User::factory()->create()->assignRole('guardian');
    }

    public function test_setup_pages_link_to_every_setup_section(): void
    {
        $this->actingAs($this->admin)->get('/setup/subjects')
            ->assertOk()
            ->assertSee(route('setup.index'))
            ->assertSee(route('setup.classes'))
            ->assertSee(route('setup.subjects'))
            ->assertSee(route('setup.assignments'));
    }

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['/setup', '/setup/classes', '/setup/subjects', '/setup/assignments'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_users_without_the_setup_permission_are_forbidden(): void
    {
        foreach (['/setup', '/setup/classes', '/setup/subjects', '/setup/assignments'] as $url) {
            $this->actingAs($this->guardian)->get($url)->assertForbidden();
            $this->actingAs($this->teacher)->get($url)->assertForbidden();
        }
    }

    public function test_admins_can_access_setup_pages(): void
    {
        foreach (['/setup', '/setup/classes', '/setup/subjects', '/setup/assignments'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_an_academic_year(): void
    {
        $response = $this->actingAs($this->admin)->post('/setup/years', [
            'name' => '2027/2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-07-31',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/setup/years');
        $this->assertDatabaseHas('academic_years', ['name' => '2027/2028', 'is_active' => true]);
    }

    public function test_only_one_year_is_active_at_a_time(): void
    {
        $first = AcademicYear::factory()->active()->create(['name' => '2025/2026']);

        $this->actingAs($this->admin)->post('/setup/years', [
            'name' => '2027/2028',
            'is_active' => '1',
        ]);

        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue(AcademicYear::where('name', '2027/2028')->first()->is_active);
    }

    public function test_year_names_must_be_unique(): void
    {
        AcademicYear::factory()->create(['name' => '2025/2026']);

        $this->actingAs($this->admin)->post('/setup/years', ['name' => '2025/2026'])
            ->assertInvalid(['name']);
    }

    public function test_terms_belong_to_their_year_in_a_unique_list(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2025/2026']);

        $this->actingAs($this->admin)->post("/setup/years/{$year->id}/terms", [
            'name' => 'Term 1',
        ])->assertRedirect("/setup/years/{$year->id}/terms");

        $this->actingAs($this->admin)->post("/setup/years/{$year->id}/terms", [
            'name' => 'Term 1',
        ])->assertInvalid(['name']);

        $this->assertDatabaseHas('terms', ['academic_year_id' => $year->id, 'name' => 'Term 1']);
    }

    public function test_classes_require_an_existing_year_and_unique_name_per_year(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        $classTeacher = User::factory()->create()->assignRole('teacher');

        $this->actingAs($this->admin)->post('/setup/classes', [
            'academic_year_id' => 999,
            'name' => 'Grade 7A',
            'class_teacher_id' => $classTeacher->id,
        ])->assertInvalid(['academic_year_id']);

        $this->actingAs($this->admin)->post('/setup/classes', [
            'academic_year_id' => $year->id,
            'name' => 'Grade 7A',
            'class_teacher_id' => $classTeacher->id,
        ])->assertRedirect('/setup/classes');

        $this->actingAs($this->admin)->post('/setup/classes', [
            'academic_year_id' => $year->id,
            'name' => 'Grade 7A',
            'class_teacher_id' => $classTeacher->id,
        ])->assertInvalid(['name']);
    }

    public function test_subject_codes_must_be_unique(): void
    {
        $this->actingAs($this->admin)->post('/setup/subjects', [
            'name' => 'Biology',
            'code' => 'BIO',
        ])->assertRedirect('/setup/subjects');

        $this->actingAs($this->admin)->post('/setup/subjects', [
            'name' => 'Advanced Biology',
            'code' => 'BIO',
        ])->assertInvalid(['code']);
    }

    public function test_one_subject_can_only_be_assigned_once_per_class(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        $class = SchoolClass::factory()->forYear($year, 'Grade 7A')->create();
        $subject = Subject::factory()->create();
        $teacher = User::factory()->create()->assignRole('teacher');
        $teacher->subjects()->attach($subject);

        $this->actingAs($this->admin)->post('/setup/assignments', [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ])->assertRedirect('/setup/assignments');

        $this->actingAs($this->admin)->post('/setup/assignments', [
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
        ])->assertInvalid(['subject_id']);

        $this->assertSame(1, Assignment::where('school_class_id', $class->id)->count());
    }

    public function test_years_with_classes_cannot_be_deleted(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        SchoolClass::factory()->forYear($year, 'Grade 7A')->create();

        $this->actingAs($this->admin)->delete("/setup/years/{$year->id}")
            ->assertSessionHasErrors(['name']);

        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
    }

    public function test_assignments_cascade_with_their_class(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        $class = SchoolClass::factory()->forYear($year, 'Grade 7A')->create();
        $subject = Subject::factory()->create();
        Assignment::factory()->forClass($class)->forSubject($subject)->create();

        // Demo seed data (created outside this test's transaction) also
        // holds assignment rows; only the cascade of ours matters here.
        $before = Assignment::count();
        $this->assertSame(1, Assignment::where('school_class_id', $class->id)->count());

        $class->delete();

        $this->assertDatabaseCount('assignments', $before - 1);
        $this->assertDatabaseMissing('assignments', ['school_class_id' => $class->id]);
    }

    public function test_setup_is_hidden_from_users_without_the_permission(): void
    {
        $this->actingAs($this->guardian)->get('/dashboard')
            ->assertOk()
            ->assertDontSee(route('setup.index'));
    }
}
