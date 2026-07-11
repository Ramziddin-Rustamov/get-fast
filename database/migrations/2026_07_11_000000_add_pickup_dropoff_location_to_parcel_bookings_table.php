<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Posilkani olib ketish (pickup) va topshirish (dropoff) nuqtalari.
     * Passengerlardagi latitude/longitude kabi decimal(10,7) formatida.
     */
    public function up(): void
    {
        Schema::table('parcel_bookings', function (Blueprint $table) {
            $table->decimal('pickup_lat', 10, 7)->nullable()->after('receiver_phone');
            $table->decimal('pickup_long', 10, 7)->nullable()->after('pickup_lat');
            $table->decimal('dropoff_lat', 10, 7)->nullable()->after('pickup_long');
            $table->decimal('dropoff_long', 10, 7)->nullable()->after('dropoff_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_bookings', function (Blueprint $table) {
            $table->dropColumn(['pickup_lat', 'pickup_long', 'dropoff_lat', 'dropoff_long']);
        });
    }
};
