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
            $table->enum('relay_state', ['on', 'off'])->default('off');
            $table->unsignedBigInteger('triggered_by')->nullable(); // Admin who toggled the relay
            $table->timestamp('recorded_at'); // When the action was recorded
            $table->timestamps();

            // Optional: foreign keys
            // $table->foreign('buoy_id')->references('id')->on('buoys')->onDelete('cascade');
            // $table->foreign('triggered_by')->references('id')->on('admins')->onDelete('set null');
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
