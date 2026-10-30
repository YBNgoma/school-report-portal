<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGuardianTest extends TestCase
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

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/students')->assertRedirect('/login');
        $this->get('/guardians')->assertRedirect('/login');
    }

    public function test_users_without_the_permission_are_forbidden(): void
    {
        $this->actingAs($this->guardian)->get('/students')->assertForbidden();
        $this->actingAs($this->guardian)->get('/guardians')->assertForbidden();
        $this->actingAs($this->teacher)->get('/guardians')->assertForbidden();
    }

    public function test_teachers_can_open_the_students_page_to_enrol_students(): void
    {
        $this->actingAs($this->teacher)->get('/students')->assertOk();
    }

    public function test_admins_can_access_student_and_guardian_pages(): void
    {
        $this->actingAs($this->admin)->get('/students')->assertOk();
        $this->actingAs($this->admin)->get('/guardians')->assertOk();
    }

    public function test_admin_can_create_a_student_linked_to_guardians(): void
    {
        $class = SchoolClass::factory()->create();
        $guardian = Guardian::factory()->create();

        $this->actingAs($this->admin)->post('/students', [
            'admission_number' => 'ADM-1001',
            'name' => 'John Doe',
            'date_of_birth' => '2015-03-04',
            'school_class_id' => $class->id,
            'guardian_ids' => [$guardian->id],
        ])->assertRedirect('/students');

        $student = Student::where('admission_number', 'ADM-1001')->firstOrFail();

        $this->assertSame($class->id, $student->school_class_id);
        $this->assertTrue($student->guardians->contains($guardian));
    }

    public function test_admission_numbers_must_be_unique(): void
    {
        Student::factory()->create(['admission_number' => 'ADM-2001']);

        $this->actingAs($this->admin)->post('/students', [
            'admission_number' => 'ADM-2001',
            'name' => 'Duplicate',
        ])->assertInvalid(['admission_number']);
    }

    public function test_student_guardian_links_can_be_updated(): void
    {
        $student = Student::factory()->create();
        $first = Guardian::factory()->create();
        $second = Guardian::factory()->create();
        $student->guardians()->sync([$first->id]);

        $this->actingAs($this->admin)->put("/students/{$student->id}", [
            'admission_number' => $student->admission_number,
            'name' => $student->name,
            'guardian_ids' => [$second->id],
        ])->assertRedirect('/students');

        $this->assertEquals([$second->id], $student->fresh()->guardians->pluck('id')->all());
    }

    public function test_admin_can_create_and_delete_a_guardian(): void
    {
        $this->actingAs($this->admin)->post('/guardians', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ])->assertRedirect('/guardians');

        $guardian = Guardian::where('name', 'Jane Doe')->firstOrFail();

        $this->actingAs($this->admin)->delete("/guardians/{$guardian->id}")
            ->assertRedirect('/guardians');

        $this->assertDatabaseMissing('guardians', ['id' => $guardian->id]);
    }

    public function test_deleting_a_guardian_keeps_the_student(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();
        $student->guardians()->attach($guardian);

        $this->actingAs($this->admin)->delete("/guardians/{$guardian->id}");

        $this->assertDatabaseHas('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('guardian_student', ['guardian_id' => $guardian->id]);
    }
}
