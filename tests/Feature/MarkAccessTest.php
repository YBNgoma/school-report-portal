<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole('admin');
        $this->teacher = User::factory()->create()->assignRole('teacher');
        $this->otherTeacher = User::factory()->create()->assignRole('teacher');

        $this->ownAssignment = Assignment::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->otherAssignment = Assignment::factory()->create(['teacher_id' => $this->otherTeacher->id]);
    }

    public function test_teachers_only_see_their_own_assignments_on_the_marks_page(): void
    {
        $this->actingAs($this->teacher)->get('/marks')
            ->assertOk()
            ->assertSee($this->ownAssignment->subject->name)
            ->assertDontSee($this->otherAssignment->subject->name);
    }

    public function test_teachers_cannot_open_another_teachers_assignment(): void
    {
        $this->actingAs($this->teacher)
            ->get('/marks?assignment='.$this->otherAssignment->id)
            ->assertNotFound();
    }

    public function test_admins_can_open_any_assignment(): void
    {
        $this->actingAs($this->admin)
            ->get('/marks?assignment='.$this->otherAssignment->id)
            ->assertOk();
    }
}
