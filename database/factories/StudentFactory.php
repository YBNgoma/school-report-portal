<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admission_number' => 'ADM-'.fake()->unique()->numerify('######'),
            'name' => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-16 years', '-6 years')->format('Y-m-d'),
            'school_class_id' => null,
        ];
    }
}
