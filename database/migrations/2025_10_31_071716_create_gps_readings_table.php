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
        Schema::create('gps_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buoy_id'); // foreign key reference to buoys table (optional)
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamp('recorded_at');
            $table->timestamps();

            // Optional: if you have a buoys table and want to enforce FK constraint
            // $table->foreign('buoy_id')->references('id')->on('buoys')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_readings');
    }
};
