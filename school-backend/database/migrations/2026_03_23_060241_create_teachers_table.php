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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('teacher_id_card')->unique();
            $table->string('name_kh');
            $table->string('name_en')->nullable();
            $table->string('specialty')->nullable();
            $table->string('gender');
            $table->date('dob')->nullable();
            $table->string('pob_province')->nullable();
            $table->string('pob_district')->nullable();
            $table->string('pob_commune')->nullable();
            $table->string('pob_village')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('commune')->nullable();
            $table->string('village')->nullable();
            $table->string('photo')->nullable();
            $table->date('hire_date')->nullable();
            $table->boolean('status')->default(1);
            
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
        Schema::dropIfExists('teachers');
    }
};