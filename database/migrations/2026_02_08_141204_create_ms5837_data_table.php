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
        Schema::create('ms5837_data', function (Blueprint $table) {
            $table->id();

            // Foreign key to Buoy
            $table->unsignedBigInteger('buoy_id');

            // Temperature
            $table->decimal('temperature_celsius', 8, 2);
            $table->decimal('temperature_fahrenheit', 8, 2);

            // Depth
            $table->decimal('depth_m', 10, 3);
            $table->decimal('depth_ft', 10, 3);

            // Water metrics
            $table->decimal('water_altitude', 10, 3);
            $table->decimal('water_pressure', 10, 3);

            // Sensor timestamp
            $table->timestamp('recorded_at');

            // Laravel timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms5837_data');
    }
};
