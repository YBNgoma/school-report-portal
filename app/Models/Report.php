<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    public const DRAFT = 'draft';

    public const COMPILED = 'compiled';

    public const APPROVED = 'approved';

    public const PUBLISHED = 'published';

    protected $fillable = ['student_id', 'term_id', 'status', 'average', 'compiled_at', 'approved_at', 'published_at'];

    protected function casts(): array
    {
        return [
            'average' => 'decimal:2',
            'compiled_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
