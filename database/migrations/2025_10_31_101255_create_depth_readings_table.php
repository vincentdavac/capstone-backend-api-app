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
        Schema::create('depth_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buoy_id');
            $table->decimal('pressure_mbar', 10, 2);
            $table->decimal('pressure_hpa', 10, 2);
            $table->decimal('depth_m', 10, 2);
            $table->decimal('depth_ft', 10, 2);
            $table->decimal('water_altitude', 10, 2)->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depth_readings');
    }
};
