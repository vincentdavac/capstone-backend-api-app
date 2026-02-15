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
        Schema::create('bme280_data', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('buoy_id');

            $table->float('temperature_celsius', 8, 2);
            $table->float('temperature_fahrenheit', 8, 2);
            $table->float('humidity', 8, 2);

            $table->float('pressure_mbar', 10, 2);
            $table->float('pressure_hpa', 10, 2);

            $table->float('altitude', 10, 2);

            $table->timestamp('recorded_at');

            $table->timestamps();

            // Optional: foreign key if you have a buoys table
            // $table->foreign('buoy_id')->references('id')->on('buoys')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bme280_data');
    }
};
