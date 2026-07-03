<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete(); 
            $table->foreignId('teacher_id')->nullable()->constrained()->cascadeOnDelete(); 
            $table->decimal('score_value', 5, 2); 
            $table->decimal('average_score', 5, 2)->nullable();
            $table->integer('rank')->nullable();
            $table->string('grade_kh', 10)->nullable();
            $table->string('grade_en', 5)->nullable();
            $table->string('remark', 100)->nullable();
            $table->text('note')->nullable();
            $table->enum('type', ['subject', 'total'])->default('subject');
            $table->timestamps();
            $table->index('teacher_id');
            $table->index(['exam_id', 'student_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};