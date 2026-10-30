<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One mark per student, per subject assignment, per term.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable(); // 0-100
            $table->timestamps();

            $table->unique(['student_id', 'assignment_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
