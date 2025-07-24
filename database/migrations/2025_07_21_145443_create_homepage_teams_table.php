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
        Schema::create('homepage_teams', function (Blueprint $table) {
            $table->id();
            $table->longText('name');
            $table->longText('role');
            $table->longText('image')->nullable();
            $table->longText('image_url')->nullable();
            $table->longText('facebook_link')->nullable();
            $table->longText('twitter_link')->nullable();
            $table->longText('linkedin_link')->nullable();
            $table->longText('instagram_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_teams');
    }
};
