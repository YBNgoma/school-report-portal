<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance per student per term, shown on the term report.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('days_present')->default(0);
            $table->unsignedSmallInteger('days_total')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
