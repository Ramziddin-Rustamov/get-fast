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
        Schema::create('payment_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // kartaning egasi
            $table->string('card_id')->unique(); // bankdan qaytgan ID
            $table->string('masked_pan');
            $table->string('expiry');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(false); // << Faqat 1ta karta active bo‘ladi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_cards');
    }
};
