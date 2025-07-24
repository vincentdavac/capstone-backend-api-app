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
        Schema::create('homepage_footers', function (Blueprint $table) {
            $table->id();
            $table->longText('email')->nullable();
            $table->longText('phone')->nullable();
            $table->longText('location')->nullable();
            $table->longText('facebook_link')->nullable();
            $table->longText('twitter_link')->nullable();
            $table->longText('linkedin_link')->nullable();
            $table->longText('instagram_link')->nullable();
            $table->longText('short_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_footers');
    }
};
