<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classroom_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('year_id')->constrained('years')->cascadeOnDelete(); // ចាប់ឆ្នាំសិក្សាជាមួយតែម្តង
            $table->string('status')->default('Studying'); // Studying, Completed, Dropped
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classroom_student');
    }
};
