<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * parcel_bookings jadvalini to'liq booking oqimiga moslash:
     *  - trip_id       — to'g'ridan-to'g'ri safar bilan bog'lash (haydovchi so'rovlari uchun)
     *  - parcel_type_id — mijoz yuborayotgan pochta turi
     *  - length/width/height — mijoz posilkasining o'lchami (sm)
     *  - status enumiga 'rejected' va 'delivered' qo'shildi
     */
    public function up(): void
    {
        Schema::table('parcel_bookings', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('parcel_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('parcel_type_id')->nullable()->after('trip_id')
                ->constrained()->nullOnDelete();
            $table->unsignedInteger('length')->nullable()->after('weight'); // sm
            $table->unsignedInteger('width')->nullable()->after('length');  // sm
            $table->unsignedInteger('height')->nullable()->after('width');  // sm
        });

        DB::statement(
            "ALTER TABLE parcel_bookings
             MODIFY status ENUM('pending','confirmed','rejected','cancelled','delivered')
             NOT NULL DEFAULT 'pending'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trip_id');
            $table->dropConstrainedForeignId('parcel_type_id');
            $table->dropColumn(['length', 'width', 'height']);
        });

        DB::statement(
            "ALTER TABLE parcel_bookings
             MODIFY status ENUM('pending','confirmed','cancelled')
             NOT NULL DEFAULT 'pending'"
        );
    }
};
