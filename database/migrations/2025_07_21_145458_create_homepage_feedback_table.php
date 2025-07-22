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
    $table->string('name', 255);
    $table->string('role', 255);
    $table->string('image', 255);
    $table->string('image_link', 255)->nullable();
    $table->integer('rate'); // 1-5 (validate in controller)
    $table->string('feedback', 255);
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
