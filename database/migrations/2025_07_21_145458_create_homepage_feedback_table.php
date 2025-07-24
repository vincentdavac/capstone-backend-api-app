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
        Schema::create('homepage_feedbacks', function (Blueprint $table) {
    $table->id();
    $table->longText('name');
    $table->longText('role');
    $table->longText('image')->nullable();
    $table->longText('image_url')->nullable();
    $table->integer('rate'); // 1-5 (validate in controller)
    $table->longText('feedback');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_feedback');
    }
};
