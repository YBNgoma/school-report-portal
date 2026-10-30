<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One report card per student per term.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft'); // draft, compiled, approved, published
            $table->decimal('average', 5, 2)->nullable();
            $table->timestamp('compiled_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
