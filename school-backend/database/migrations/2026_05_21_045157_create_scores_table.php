<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id(); 
            
            
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            
            $table->decimal('score_value', 5, 2)->nullable();
            $table->decimal('total_score', 6, 2); 
            $table->decimal('average', 5, 2);     
            $table->integer('rank');              
            $table->string('result'); 
            $table->string('note')->nullable(); 
            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'subject_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('scores');
    }
};
