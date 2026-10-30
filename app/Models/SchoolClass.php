<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['academic_year_id', 'name', 'class_teacher_id'];

    protected $table = 'school_classes';

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
