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
        Schema::create('homepage_prototype_file', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // name of the 3D model
            $table->string('attachment'); // stores the .glb file name or path
            $table->boolean('is_archived')->default(false); // archive flag
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_prototype_file');
    }
};
