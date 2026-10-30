<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Mark;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $guardian;

    private Term $term;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole('admin');
        $this->teacher = User::factory()->create()->assignRole('teacher');
        $this->guardian = User::factory()->create()->assignRole('guardian');

        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        $this->term = Term::factory()->forYear($year, 'Term 1')->create();
        $class = SchoolClass::factory()->forYear($year, 'Grade 7A')->create();

        $this->student = Student::factory()->create(['school_class_id' => $class->id]);

        // Two subjects with scores 60 and 80 -> average 70.
        foreach ([60, 80] as $score) {
            $assignment = Assignment::factory()
                ->forClass($class)
                ->forSubject(Subject::factory()->create())
                ->taughtBy($this->teacher->id)
                ->create();

            Mark::create([
                'student_id' => $this->student->id,
                'assignment_id' => $assignment->id,
                'term_id' => $this->term->id,
                'score' => $score,
            ]);
        }
    }

    public function test_compile_averages_the_students_marks(): void
    {
        $this->actingAs($this->teacher)->post('/reports/compile', [
            'term_id' => $this->term->id,
        ])->assertRedirect();

        $report = Report::where('student_id', $this->student->id)->where('term_id', $this->term->id)->firstOrFail();

        $this->assertSame(Report::COMPILED, $report->status);
        $this->assertEquals(70, (float) $report->average);
    }

    public function test_approved_reports_are_not_recompiled(): void
    {
        $this->actingAs($this->teacher)->post('/reports/compile', ['term_id' => $this->term->id]);

        $report = Report::where('student_id', $this->student->id)->firstOrFail();
        $this->actingAs($this->admin)->post("/reports/{$report->id}/approve");

        Mark::where('student_id', $this->student->id)->update(['score' => 10]);
        $this->actingAs($this->teacher)->post('/reports/compile', ['term_id' => $this->term->id]);

        $report->refresh();
        $this->assertSame(Report::APPROVED, $report->status);
        $this->assertEquals(70, (float) $report->average);
    }

    public function test_workflow_runs_compile_then_approve_then_publish(): void
    {
        $this->actingAs($this->teacher)->post('/reports/compile', ['term_id' => $this->term->id]);
        $report = Report::where('student_id', $this->student->id)->firstOrFail();

        // Publishing a compiled (unapproved) report is refused.
        $this->actingAs($this->admin)->post("/reports/{$report->id}/publish")
            ->assertSessionHasErrors('status');
        $this->assertSame(Report::COMPILED, $report->fresh()->status);

        $this->actingAs($this->admin)->post("/reports/{$report->id}/approve");
        $this->assertSame(Report::APPROVED, $report->fresh()->status);

        $this->actingAs($this->admin)->post("/reports/{$report->id}/publish");
        $this->assertSame(Report::PUBLISHED, $report->fresh()->status);
    }

    public function test_teachers_cannot_approve_or_publish(): void
    {
        $this->actingAs($this->teacher)->post('/reports/compile', ['term_id' => $this->term->id]);
        $report = Report::where('student_id', $this->student->id)->firstOrFail();

        $this->actingAs($this->teacher)->post("/reports/{$report->id}/approve")->assertForbidden();
        $this->actingAs($this->teacher)->post("/reports/{$report->id}/publish")->assertForbidden();
    }

    public function test_compiled_report_downloads_as_pdf(): void
    {
        $this->actingAs($this->teacher)->post('/reports/compile', ['term_id' => $this->term->id]);
        $report = Report::where('student_id', $this->student->id)->firstOrFail();

        $response = $this->actingAs($this->teacher)->get("/reports/{$report->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_guardians_cannot_download_report_pdfs(): void
    {
        $report = Report::create([
            'student_id' => $this->student->id,
            'term_id' => $this->term->id,
            'status' => Report::COMPILED,
        ]);

        $this->actingAs($this->guardian)->get("/reports/{$report->id}/pdf")->assertForbidden();
    }

    public function test_guardians_cannot_access_reports(): void
    {
        $this->actingAs($this->guardian)->get('/reports')->assertForbidden();
        $this->actingAs($this->guardian)->post('/reports/compile', ['term_id' => $this->term->id])->assertForbidden();
    }
}
