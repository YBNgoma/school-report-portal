<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => null,
        ];
    }

    public function forClass(SchoolClass $class): static
    {
        return $this->state(fn () => ['school_class_id' => $class->id]);
    }

    public function forSubject(Subject $subject): static
    {
        return $this->state(fn () => ['subject_id' => $subject->id]);
    }

    public function taughtBy(int $userId): static
    {
        return $this->state(fn () => ['teacher_id' => $userId]);
    }
}
