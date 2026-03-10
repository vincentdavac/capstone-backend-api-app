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
        Schema::create('recent_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alertId')->unique();
            $table->string('buoy_id');
            $table->string('description');
            $table->string('alert_level');
            $table->string('sensor_type');
            $table->boolean('alert_shown')->default(false);
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_alerts');
    }
};
