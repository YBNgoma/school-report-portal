<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School setup: academic years, terms, classes, subjects and
 * subject/teacher assignments per class.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. "2026/2027"
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Term 1"
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'name']);
        });

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Grade 7A"
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'name']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Mathematics"
            $table->string('code')->unique(); // e.g. "MATH"
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_class_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_years');
    }
};
