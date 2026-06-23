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
        Schema::create('hourly_energy_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->timestamp('logged_at');
            $table->decimal('voltage', 8, 2);
            $table->decimal('current', 8, 4);
            $table->decimal('power', 8, 2);
            $table->decimal('energy', 10, 4); // energy in kWh consumed today up to this hour
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_energy_logs');
    }
};
