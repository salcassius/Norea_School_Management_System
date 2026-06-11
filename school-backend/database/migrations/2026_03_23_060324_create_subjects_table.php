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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            
            // --- ឈ្មោះមុខវិជ្ជា (ឧទាហរណ៍៖ គណិតវិទ្យា, អក្សរសាស្ត្រខ្មែរ) ---
            $table->string('name');

            // --- Foreign Keys (ទំនាក់ទំនង) ---
            // បន្ថែមគ្រូបង្គោលប្រចាំមុខវិជ្ជា (អនុញ្ញាតឱ្យ Null ករណីមិនទាន់មានគ្រូបង្រៀន)
            $table->foreignId('teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->nullOnDelete(); 

            // ភ្ជាប់មុខវិជ្ជានេះទៅកាន់ថ្នាក់រៀនជាក់លាក់ណាមួយ
            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->cascadeOnDelete();

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
        Schema::dropIfExists('subjects');
    }
};