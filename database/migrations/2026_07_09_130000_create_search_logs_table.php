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
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();

            // Kim qidirdi (mehmon bo'lsa null) — marketing uchun
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Qayerdan (ID lar)
            $table->unsignedBigInteger('start_region_id')->nullable();
            $table->unsignedBigInteger('start_district_id')->nullable();
            $table->unsignedBigInteger('start_quarter_id')->nullable();

            // Qayerga (ID lar)
            $table->unsignedBigInteger('end_region_id')->nullable();
            $table->unsignedBigInteger('end_district_id')->nullable();
            $table->unsignedBigInteger('end_quarter_id')->nullable();

            // Admin uchun o'qiladigan manzil nomlari (qidiruv paytidagi holat)
            $table->string('start_location')->nullable();
            $table->string('end_location')->nullable();

            // Qo'shimcha qidiruv parametrlari
            $table->dateTime('departure_date')->nullable();
            $table->boolean('is_round_trip')->default(false);
            $table->dateTime('return_date')->nullable();

            // Topilgan safarlar soni (marketing tahlili uchun)
            $table->unsignedInteger('results_count')->default(0);

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
