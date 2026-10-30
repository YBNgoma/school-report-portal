<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which subjects a teacher is qualified to teach.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subject_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_user');
    }
};
