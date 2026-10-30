<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Term>
 */
class TermFactory extends Factory
{
    private static int $index = 0;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 years', 'now');

        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Term '.(self::$index % 3 + 1),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $start->modify('+3 months')->format('Y-m-d'),
        ];
    }

    public function forYear(AcademicYear $year, ?string $name = null): static
    {
        return $this->state(fn () => array_filter([
            'academic_year_id' => $year->id,
            'name' => $name,
        ]));
    }
}
