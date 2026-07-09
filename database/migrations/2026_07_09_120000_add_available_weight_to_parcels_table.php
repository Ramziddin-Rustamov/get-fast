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
     * available_weight — safarda hali bo'sh bo'lgan pochta sig'imi (kg).
     * Boshlanishida max_weight ga teng, har posilka bilan kamayadi.
     */
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->float('available_weight')->nullable()->after('max_weight');
        });

        // Mavjud parcellar uchun available_weight = max_weight
        DB::table('parcels')->update([
            'available_weight' => DB::raw('max_weight'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropColumn('available_weight');
        });
    }
};
