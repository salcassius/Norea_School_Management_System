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

            // សោក្រៅ (Foreign Keys) ភ្ជាប់ទៅកាន់តារាងផ្សេងៗ
            $table->foreignId('year_id')->constrained('years')->onDelete('cascade'); // ឆ្នាំសិក្សា
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');               // ថ្នាក់រៀន
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');           // មុខវិជ្ជា
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');           // គ្រូបង្រៀន

            // ព័ត៌មានលម្អិតនៃកាលវិភាគដែលស៊ីគ្នាជាមួយ UI Frontend
            $table->string('day');         // សម្រាប់ទទួលតម្លៃ៖ 'ច័ន្ទ', 'អង្គារ', 'ពុធ'...
            $table->string('time');        // ប្តូរមកប្រើ 'time' ឱ្យត្រូវនឹង UI (ឧទាហរណ៍៖ '07:00 - 08:00')
            $table->string('shift');       // សម្រាប់ទទួលតម្លៃ៖ 'ព្រឹក', 'រសៀល'
            $table->string('room')->nullable(); // ប្តូរមកប្រើ 'room' ឱ្យត្រូវនឹង UI (ឧទាហរណ៍៖ 'A-101')

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
