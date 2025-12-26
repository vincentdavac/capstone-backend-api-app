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
        Schema::create('hotlines', function (Blueprint $table) {
            $table->id();
            $table->enum('created_by_role', ['admin', 'barangay']);
            $table->unsignedBigInteger('barangay_id')->nullable();
            $table->string('number', 50);
            $table->string('description', 255);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotlines');
    }
};
