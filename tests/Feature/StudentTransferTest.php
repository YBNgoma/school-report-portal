<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentTransferTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole('admin');
    }

    public function test_export_downloads_students_as_csv(): void
    {
        Student::factory()->create([
            'admission_number' => 'ADM-3001',
            'name' => 'Amara Okafor',
            'date_of_birth' => '2015-03-04',
        ]);

        $response = $this->actingAs($this->admin)->get('/students/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString("admission_number,name,date_of_birth\n", $response->streamedContent());
        $this->assertStringContainsString('ADM-3001,"Amara Okafor",2015-03-04', $response->streamedContent());
    }

    public function test_import_creates_valid_rows_and_skips_invalid_ones(): void
    {
        Student::factory()->create(['admission_number' => 'ADM-4001']);

        $csv = implode("\n", [
            'admission_number,name,date_of_birth',
            'ADM-4002,Ben Tan,2014-05-06',
            'ADM-4001,Duplicate Number,',
            ',No Admission Number,',
            'ADM-4003,Future Child,2999-01-01',
        ]);

        $this->actingAs($this->admin)->post('/students/import', [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertRedirect('/students');

        $this->assertDatabaseHas('students', ['admission_number' => 'ADM-4002', 'name' => 'Ben Tan']);
        $this->assertDatabaseMissing('students', ['name' => 'Duplicate Number']);
        $this->assertDatabaseMissing('students', ['name' => 'No Admission Number']);
        $this->assertDatabaseMissing('students', ['admission_number' => 'ADM-4003']);
        $this->assertDatabaseCount('students', 2);
    }

    public function test_import_rejects_a_file_with_the_wrong_header(): void
    {
        $csv = "name,admission_number\nBen Tan,ADM-5001\n";

        $this->actingAs($this->admin)->post('/students/import', [
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('students', 0);
    }

    public function test_users_without_the_permission_cannot_export_or_import(): void
    {
        $teacher = User::factory()->create()->assignRole('teacher');

        $this->actingAs($teacher)->get('/students/export')->assertForbidden();
        $this->actingAs($teacher)->post('/students/import', [
            'file' => UploadedFile::fake()->createWithContent('students.csv', "admission_number,name,date_of_birth\n"),
        ])->assertForbidden();
    }
}
