<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class SetupDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (AcademicYear::where('name', '2026/2027')->exists()) {
            $this->command?->line('Setup demo data already present, skipping.');

            return;
        }

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_active' => true,
        ]);

        foreach (['Term 1', 'Term 2', 'Term 3'] as $i => $term) {
            $month = [9, 1, 4][$i];
            $yearOfStart = $i === 0 ? 2026 : 2027;

            $year->terms()->create([
                'name' => $term,
                'start_date' => sprintf('%d-%02d-01', $yearOfStart, $month),
                'end_date' => sprintf('%d-%02d-30', $month <= 4 ? $yearOfStart : $yearOfStart, $month == 9 ? 12 : ($month + 2)),
            ]);
        }

        $teacher = User::whereEmail('teacher@example.com')->first();

        $subjects = collect([
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'History', 'code' => 'HIST'],
        ])->map(fn (array $s) => Subject::create($s));

        foreach (['Grade 7A', 'Grade 8A'] as $name) {
            $class = SchoolClass::create([
                'academic_year_id' => $year->id,
                'name' => $name,
                'class_teacher_id' => $teacher?->id,
            ]);

            foreach ($subjects as $subject) {
                Assignment::create([
                    'school_class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher?->id,
                ]);
            }
        }
    }
}
