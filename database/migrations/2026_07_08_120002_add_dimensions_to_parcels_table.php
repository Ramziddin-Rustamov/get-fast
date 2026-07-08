<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Haydovchi bagajiga sig'adigan eng katta o'lcham (sm).
     */
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->unsignedInteger('max_length')->nullable()->after('price_per_kg'); // uzunligi (sm)
            $table->unsignedInteger('max_width')->nullable()->after('max_length');     // eni (sm)
            $table->unsignedInteger('max_height')->nullable()->after('max_width');     // balandligi (sm)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropColumn(['max_length', 'max_width', 'max_height']);
        });
    }
};
