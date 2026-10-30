<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $otherTeacher;

    private User $guardian;

    private Assignment $ownAssignment;

    private Assignment $otherAssignment;

    private Term $term;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole('admin');
        $this->teacher = User::factory()->create()->assignRole('teacher');
        $this->otherTeacher = User::factory()->create()->assignRole('teacher');
        $this->guardian = User::factory()->create()->assignRole('guardian');

        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        $this->term = Term::factory()->forYear($year, 'Term 1')->create();
        $class = SchoolClass::factory()->forYear($year, 'Grade 7A')->create();

        $this->ownAssignment = Assignment::factory()
            ->forClass($class)
            ->forSubject(Subject::factory()->create())
            ->taughtBy($this->teacher->id)
            ->create();

        $this->otherAssignment = Assignment::factory()
            ->forClass($class)
            ->forSubject(Subject::factory()->create())
            ->taughtBy($this->otherTeacher->id)
            ->create();

        $this->student = Student::factory()->create(['school_class_id' => $class->id]);
    }

    public function test_guardians_cannot_access_mark_entry(): void
    {
        $this->actingAs($this->guardian)->get('/marks')->assertForbidden();
        $this->actingAs($this->guardian)->post('/marks', [])->assertForbidden();
    }

    public function test_teachers_only_see_their_own_assignments(): void
    {
        $this->actingAs($this->teacher)
            ->get('/marks?term='.$this->term->id.'&assignment='.$this->otherAssignment->id)
            ->assertNotFound();

        $this->actingAs($this->teacher)
            ->get('/marks?term='.$this->term->id.'&assignment='.$this->ownAssignment->id)
            ->assertOk()
            ->assertSee($this->student->name);
    }

    public function test_admins_can_load_any_assignment(): void
    {
        $this->actingAs($this->admin)
            ->get('/marks?term='.$this->term->id.'&assignment='.$this->otherAssignment->id)
            ->assertOk();
    }

    public function test_teacher_can_save_marks_for_their_own_assignment(): void
    {
        $this->actingAs($this->teacher)->post('/marks', [
            'term_id' => $this->term->id,
            'assignment_id' => $this->ownAssignment->id,
            'scores' => [$this->student->id => '72.5'],
        ])->assertRedirect();

        $this->assertDatabaseHas('marks', [
            'student_id' => $this->student->id,
            'assignment_id' => $this->ownAssignment->id,
            'term_id' => $this->term->id,
            'score' => 72.5,
        ]);
    }

    public function test_saving_updates_an_existing_mark_instead_of_duplicating(): void
    {
        Mark::create([
            'student_id' => $this->student->id,
            'assignment_id' => $this->ownAssignment->id,
            'term_id' => $this->term->id,
            'score' => 40,
        ]);

        $this->actingAs($this->teacher)->post('/marks', [
            'term_id' => $this->term->id,
            'assignment_id' => $this->ownAssignment->id,
            'scores' => [$this->student->id => '88'],
        ]);

        $this->assertSame(1, Mark::count());
        $this->assertEquals(88, Mark::first()->score);
    }

    public function test_teacher_cannot_save_marks_for_another_teachers_assignment(): void
    {
        $this->actingAs($this->teacher)->post('/marks', [
            'term_id' => $this->term->id,
            'assignment_id' => $this->otherAssignment->id,
            'scores' => [$this->student->id => '50'],
        ])->assertForbidden();

        $this->assertDatabaseCount('marks', 0);
    }

    public function test_scores_must_be_between_0_and_100(): void
    {
        $this->actingAs($this->teacher)->post('/marks', [
            'term_id' => $this->term->id,
            'assignment_id' => $this->ownAssignment->id,
            'scores' => [$this->student->id => '101'],
        ])->assertSessionHasErrors('scores.'.$this->student->id);

        $this->assertDatabaseCount('marks', 0);
    }

    public function test_scores_for_students_outside_the_class_are_ignored(): void
    {
        $outsider = Student::factory()->create(['school_class_id' => null]);

        $this->actingAs($this->teacher)->post('/marks', [
            'term_id' => $this->term->id,
            'assignment_id' => $this->ownAssignment->id,
            'scores' => [$outsider->id => '60'],
        ]);

        $this->assertDatabaseMissing('marks', ['student_id' => $outsider->id]);
    }
}
