<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per recorded action. Written only through AuditLog::record().
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'description'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $description, ?Model $subject = null): void
    {
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? strtolower(class_basename($subject)) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
        ]);
    }
}
