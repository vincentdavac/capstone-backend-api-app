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
            $table->longText('image')->nullable();
            $table->string('caption')->nullable();
            $table->longText('documentation_link')->nullable();
            $table->longText('research_paper_link')->nullable();
            $table->string('email_address')->nullable();
            $table->longText('facebook_link')->nullable();
            $table->longText('youtube_link')->nullable();
            $table->string('footer_subtitle')->nullable();
            $table->boolean('is_archived')->default(false);
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
