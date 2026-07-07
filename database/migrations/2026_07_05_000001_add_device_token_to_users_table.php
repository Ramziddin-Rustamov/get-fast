<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mobil app (Flutter) FCM device tokenini saqlash uchun.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('device_token', 512)->nullable()->after('verification_code');
            $table->string('device_platform', 20)->nullable()->after('device_token'); // android | ios
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_token', 'device_platform']);
        });
    }
};
