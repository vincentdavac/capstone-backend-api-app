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
        Schema::table('recent_alerts', function (Blueprint $table) {
             $table->boolean('alert_shown')->default(false)->after('alert_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recent_alerts', function (Blueprint $table) {
            //
        });
    }
};
