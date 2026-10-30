<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Student;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Term;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole('admin');
    }

    public function test_creating_a_student_writes_an_audit_entry(): void
    {
        $this->actingAs($this->admin)->post('/students', [
            'admission_number' => 'ADM-6001',
            'name' => 'Chidi Eze',
        ])->assertRedirect('/students');

        $student = Student::where('admission_number', 'ADM-6001')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'student.created',
            'subject_type' => 'student',
            'subject_id' => $student->id,
        ]);
    }

    public function test_publishing_a_report_writes_an_audit_entry(): void
    {
        $year = AcademicYear::factory()->create(['name' => '2025/2026']);
        $term = Term::factory()->forYear($year, 'Term 1')->create();
        $student = Student::factory()->create();
        $report = Report::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'status' => Report::APPROVED,
        ]);

        $this->actingAs($this->admin)->post("/reports/{$report->id}/publish")->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'report.published',
            'subject_type' => 'report',
            'subject_id' => $report->id,
        ]);
    }

    public function test_audit_page_lists_recent_entries_for_admins(): void
    {
        AuditLog::record('test.action', 'Something happened');

        $this->actingAs($this->admin)->get('/audit')
            ->assertOk()
            ->assertSee('Something happened');
    }

    public function test_users_without_the_permission_cannot_view_the_audit_log(): void
    {
        $teacher = User::factory()->create()->assignRole('teacher');

        $this->actingAs($teacher)->get('/audit')->assertForbidden();
    }
}
