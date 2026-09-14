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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20);
            $table->string('customer_name', 150);
            $table->enum('service_type', ['Customer Service', 'Teller', 'Helpdesk'])->default('Customer Service');
            $table->enum('status', ['Menunggu', 'Dipanggil', 'Selesai', 'Batal'])->default('Menunggu');
            $table->string('counter_number', 10)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
