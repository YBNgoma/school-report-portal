<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    public function definition(): array
    {
        // Unique per test run so names never collide; never 2026, which the
        // demo seeder owns as '2026/2027'.
        $start = fake()->unique()->numberBetween(2000, 2090);
        if ($start === 2026) {
            $start = 2091;
        }

        return [
            'name' => $start.'/'.($start + 1),
            'start_date' => "{$start}-09-01",
            'end_date' => ($start + 1).'-07-31',
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }
}
