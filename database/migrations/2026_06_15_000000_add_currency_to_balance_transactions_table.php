<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * BookingRepository::cancelBooking() balance_transactions jadvaliga 'currency'
     * qiymatini yozadi, lekin ustun create migration'da yo'q edi. Shu sababli
     * yangi migratsiya qilingan bazada cancelBooking "Unknown column 'currency'"
     * xatosini berardi. Bu migratsiya o'sha nomuvofiqlikni bartaraf etadi.
     */
    public function up(): void
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('balance_transactions', 'currency')) {
                $table->string('currency', 3)->default('UZS')->after('reference_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('balance_transactions', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};
