<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('type')->default('ប្រចាំខែ'); 
            
            // Relationships
            $table->foreignId('year_id')->constrained('years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            
            $table->date('exam_date'); // ថ្ងៃប្រឡង
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status')->default('ជិតមកដល់'); // ស្ថានភាព
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exams');
    }
};

