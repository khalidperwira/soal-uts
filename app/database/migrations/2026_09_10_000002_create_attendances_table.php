<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 50);
            $table->string('student_name', 150);
            $table->string('date', 10)->comment('Format: DD-MM-YYYY');
            $table->time('time_in');
            $table->time('time_out')->nullable();
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpha'])->default('Hadir');
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
