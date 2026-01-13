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
        Schema::create('battery_health', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buoy_id');
            $table->decimal('percentage', 5, 2);
            $table->decimal('voltage', 5, 2);
            $table->timestamps();

            // Optional: add foreign key if buoy table exists
            // $table->foreign('buoy_id')->references('id')->on('buoys')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battery_health');
    }
};
