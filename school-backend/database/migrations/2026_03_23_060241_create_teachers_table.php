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
            
            // --- Foreign Key (ទំនាក់ទំនងជាមួយ User Account) ---
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // --- ព័ត៌មានផ្ទាល់ខ្លួនគ្រូបង្រៀន ---
            $table->string('teacher_id_card')->unique();
            $table->string('name_kh');
            $table->string('name_en')->nullable();
            $table->string('specialty')->nullable();
            $table->string('gender');
            $table->date('dob')->nullable();

            // --- ទីកន្លែងកំណើត (Place of Birth - បំបែកជា ៤) ---
            $table->string('pob_province')->nullable();
            $table->string('pob_district')->nullable();
            $table->string('pob_commune')->nullable();
            $table->string('pob_village')->nullable();

            // --- ព័ត៌មានទំនាក់ទំនង ---
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // --- អាសយដ្ឋានបច្ចុប្បន្ន (Current Address - បំបែកជា ៤) ---
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('commune')->nullable();
            $table->string('village')->nullable();

            // --- ព័ត៌មានការងារសាលា ---
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