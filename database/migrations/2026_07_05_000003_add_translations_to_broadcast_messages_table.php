<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ko'p tilli e'lon: { "uz": {"title":..,"body":..}, "ru": {...}, "en": {...} }
     * `title`/`body` ustunlari uz (asosiy) nusxa sifatida saqlanib qoladi.
     */
    public function up(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }
};
