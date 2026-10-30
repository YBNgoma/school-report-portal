<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    private User $guardianUser;

    private Term $term;

    private Student $child;

    private Student $otherChild;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->guardianUser = User::factory()->create()->assignRole('guardian');

        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        $this->term = Term::factory()->forYear($year, 'Term 1')->create();
        $class = SchoolClass::factory()->forYear($year, 'Grade 7A')->create();

        $this->child = Student::factory()->create(['school_class_id' => $class->id]);
        $this->otherChild = Student::factory()->create(['school_class_id' => $class->id]);

        $guardian = Guardian::factory()->create(['user_id' => $this->guardianUser->id]);
        $guardian->students()->attach($this->child);
    }

    public function test_guardian_sees_only_published_reports_for_linked_children(): void
    {
        $published = $this->report($this->child, Report::PUBLISHED);
        $secondTerm = Term::factory()->forYear($this->term->academicYear, 'Term 2')->create();
        Report::create([
            'student_id' => $this->child->id,
            'term_id' => $secondTerm->id,
            'status' => Report::COMPILED,
        ]);
        $this->report($this->otherChild, Report::PUBLISHED);

        $response = $this->actingAs($this->guardianUser)->get('/portal');

        $response->assertOk();
        $response->assertSee(route('portal.reports.pdf', $published));
        $this->assertCount(1, $response->viewData('reports'));
    }

    public function test_guardian_can_download_a_published_report_for_their_child(): void
    {
        $report = $this->report($this->child, Report::PUBLISHED);

        $response = $this->actingAs($this->guardianUser)->get("/portal/reports/{$report->id}/pdf");

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_guardian_cannot_download_an_unpublished_report(): void
    {
        $report = $this->report($this->child, Report::APPROVED);

        $this->actingAs($this->guardianUser)->get("/portal/reports/{$report->id}/pdf")->assertNotFound();
    }

    public function test_guardian_cannot_download_another_childs_report(): void
    {
        $report = $this->report($this->otherChild, Report::PUBLISHED);

        $this->actingAs($this->guardianUser)->get("/portal/reports/{$report->id}/pdf")->assertNotFound();
    }

    public function test_users_without_the_portal_permission_are_forbidden(): void
    {
        $teacher = User::factory()->create()->assignRole('teacher');
        $report = $this->report($this->child, Report::PUBLISHED);

        $this->actingAs($teacher)->get('/portal')->assertForbidden();
        $this->actingAs($teacher)->get("/portal/reports/{$report->id}/pdf")->assertForbidden();
    }

    private function report(Student $student, string $status): Report
    {
        return Report::create([
            'student_id' => $student->id,
            'term_id' => $this->term->id,
            'status' => $status,
            'average' => 70,
        ]);
    }
}
