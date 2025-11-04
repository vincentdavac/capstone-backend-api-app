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
        Schema::create('homepage_about_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_about_id')
                ->constrained('homepage_abouts')
                ->onDelete('cascade'); // delete cards when about is deleted
            $table->string('card_title');
            $table->longText('card_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_about_cards');
    }
};
