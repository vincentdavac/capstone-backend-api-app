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
        Schema::table('buoys', function (Blueprint $table) {
            $table->foreign('barangay_id')
                  ->references('id')
                  ->on('barangays')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('buoys', function (Blueprint $table) {
            $table->dropForeign(['barangay_id']);
        });
    }

};
