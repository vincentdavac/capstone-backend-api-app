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
        Schema::create('wind_readings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('buoy_id');

            // Wind speed
            $table->decimal('wind_speed_m_s', 6, 2);
            $table->decimal('wind_speed_k_h', 6, 2);

            // Sensor timestamp
            $table->timestamp('recorded_at');

            // Laravel timestamps
            $table->timestamps(); // creates `created_at` and `updated_at` with automatic current timestamps
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wind_readings');
    }
};
