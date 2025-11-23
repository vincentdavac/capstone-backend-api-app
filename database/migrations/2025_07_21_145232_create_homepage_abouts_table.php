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
            $table->longText('video_link')->nullable();
            $table->longText('image')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->longText('side_title')->nullable();
            $table->longText('side_description')->nullable();
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
