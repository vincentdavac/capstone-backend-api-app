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
        Schema::create('homepage_abouts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('caption', 255);
            $table->string('image', 255);
            $table->string('image_link')->nullable();
            $table->string('side_title', 255);
            $table->string('side_description', 255);
            $table->string('first_card_title', 255);
            $table->string('first_card_description', 255);
            $table->string('second_card_title', 255);
            $table->string('second_card_description', 255);
            $table->string('third_card_title', 255);
            $table->string('third_card_description', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_abouts');
    }
};
