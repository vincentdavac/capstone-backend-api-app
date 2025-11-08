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
        Schema::create('relay_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buoy_id');
            $table->boolean('relay_state')->default(false);
            $table->timestamps();

            // Optional: foreign key to buoys table
            // $table->foreign('buoy_id')->references('id')->on('buoys')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relay_status');
    }
};
