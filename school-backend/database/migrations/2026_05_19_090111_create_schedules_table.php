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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_id')->constrained('years')->onDelete('cascade'); 
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');               
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');           
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');          
            $table->string('day');         
            $table->string('time');        
            $table->string('shift');       
            $table->string('room')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};
