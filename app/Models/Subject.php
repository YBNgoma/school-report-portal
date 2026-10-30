<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Teachers qualified to teach this subject.
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subject_user')->withTimestamps();
    }
}
