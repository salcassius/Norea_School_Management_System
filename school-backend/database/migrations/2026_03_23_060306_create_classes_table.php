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
    Schema::create('classes', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('grade_level');
        $table->boolean('is_active')->default(1);
        $table->foreignId('year_id')->constrained()->cascadeOnDelete();
        $table->foreignId('teacher_id')->nullable()
              ->constrained('teachers')->nullOnDelete();
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
        Schema::dropIfExists('classes');
    }
};
