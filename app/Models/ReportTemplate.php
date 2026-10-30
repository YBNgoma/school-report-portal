<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The single report card layout used for every PDF.
 */
class ReportTemplate extends Model
{
    /** Section keys that can be shown on a report card, in print order. */
    public const SECTIONS = [
        'student_details' => 'Student details',
        'attendance' => 'Attendance',
        'academic_performance' => 'Academic performance',
        'prepared_by' => 'Prepared by (class teacher)',
    ];

    protected $fillable = ['sections'];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
        ];
    }

    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['sections' => array_keys(self::SECTIONS)],
        );
    }

    public function shows(string $section): bool
    {
        return in_array($section, $this->sections ?? [], true);
    }
}
