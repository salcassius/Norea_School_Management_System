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
    Schema::create('attendance', function (Blueprint $table) {
        $table->id();

        $table->foreignId('student_id')->constrained()->cascadeOnDelete();
        $table->foreignId('class_id')->constrained()->cascadeOnDelete();
        $table->date('date');
        $table->enum('status', ['present', 'absent', 'excused']);
        $table->text('notes')->nullable();
        $table->timestamps();
        $table->unique(['student_id', 'class_id', 'date']);
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance');
    }
};
