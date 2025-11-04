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
            $table->longText('title');
            $table->longText('description')->nullable();
            $table->longText('image')->nullable();
            $table->enum('position', ['left', 'right'])->nullable(); // Accepts only left or right
            $table->boolean('is_archived')->default(false);
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
