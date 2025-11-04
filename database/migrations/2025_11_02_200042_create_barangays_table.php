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
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('barangay_code')->unique();
            $table->longText('name');
            $table->integer('number');
            $table->double('river_wall_height')->nullable();
            $table->double('square_meter')->nullable();
            $table->double('hectare')->nullable();
            $table->double('white_level_alert')->nullable();
            $table->double('blue_level_alert')->nullable();
            $table->longText('red_level_alert')->nullable();
            $table->longText('description')->nullable();
            $table->longText('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
