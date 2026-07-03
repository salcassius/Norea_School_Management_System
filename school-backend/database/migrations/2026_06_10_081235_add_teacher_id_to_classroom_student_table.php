<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('classroom_student', function (Blueprint $table) {
            $table->foreignId('teacher_id')
                  ->nullable() 
                  ->after('class_id')
                  ->constrained('teachers')
                  ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('classroom_student', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};