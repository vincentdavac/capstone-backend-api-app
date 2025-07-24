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
            $table->longText('title');
            $table->longText('caption');
            $table->longText('image')->nullable();
            $table->longText('image_url')->nullable();
            $table->longText('side_title');
            $table->longText('side_description');
            $table->longText('first_card_title');
            $table->longText('first_card_description');
            $table->longText('second_card_title');
            $table->longText('second_card_description');
            $table->longText('third_card_title');
            $table->longText('third_card_description');
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
