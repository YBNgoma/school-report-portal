<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Class '.fake()->unique()->numberBetween(1, 100000),
            'class_teacher_id' => null,
        ];
    }

    public function forYear(AcademicYear $year, ?string $name = null): static
    {
        return $this->state(fn () => array_filter([
            'academic_year_id' => $year->id,
            'name' => $name,
        ]));
    }

    public function taughtBy(int $userId): static
    {
        return $this->state(fn () => ['class_teacher_id' => $userId]);
    }
}
