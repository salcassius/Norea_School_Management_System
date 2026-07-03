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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('student_id_card')->nullable()->unique();
            $table->string('name_kh');
            $table->string('name_en')->nullable();
            $table->string('photo')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('pob_province')->nullable();
            $table->string('pob_district')->nullable();
            $table->string('pob_commune')->nullable();
            $table->string('pob_village')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('commune')->nullable();
            $table->string('village')->nullable();
            $table->string('guardian_mom_name')->nullable();
            $table->string('guardian_mom_job')->nullable();
            $table->string('guardian_mom_phone')->nullable();
            $table->string('guardian_dad_name')->nullable();
            $table->string('guardian_dad_job')->nullable();
            $table->string('guardian_dad_phone')->nullable();
            $table->date('enrollment_date')->nullable();
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
        Schema::dropIfExists('students');
    }
};