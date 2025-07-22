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
        Schema::create('homepage_prototypes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('caption', 255)->nullable();
            $table->string('image', 255);
            $table->string('image_link', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_prototypes');
    }
};
