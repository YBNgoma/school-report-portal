<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    private static int $index = 0;

    private const SUBJECTS = [
        'Mathematics' => 'MATH',
        'English' => 'ENG',
        'Science' => 'SCI',
        'History' => 'HIST',
        'Geography' => 'GEO',
        'Art' => 'ART',
    ];

    public function definition(): array
    {
        $index = ++self::$index;

        $subjects = self::SUBJECTS;
        $name = array_keys($subjects)[($index - 1) % count($subjects)];

        // Codes are unique in the database, so if the demo seeders or a
        // previous factory row already used this code, qualify the name.
        if (Subject::where('code', $subjects[$name])->exists()) {
            $code = $subjects[$name].$index;
            $name .= ' '.$index;
        } else {
            $code = $subjects[$name];
        }

        return [
            'name' => $name,
            'code' => $code,
        ];
    }
}
