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
        Schema::create('buoys', function (Blueprint $table) {
            $table->id();
            $table->string('buoy_code')->unique();
            $table->string('river_name');
            $table->double('wall_height', 8, 2);
            $table->double('river_hectare', 8, 2);
            $table->double('latitude', 10, 6);
            $table->double('longitude', 10, 6);
            $table->unsignedBigInteger('barangay');
            $table->string('attachment')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->dateTime('maintenance_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buoys');
    }
};
