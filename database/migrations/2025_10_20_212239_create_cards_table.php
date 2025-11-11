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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();

            // Foydalanuvchiga bog‘lanish (agar userlar jadvali bo‘lsa)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Hamkorbank qaytaradigan token/ID (card.verify -> result.id)
            $table->string('card_id')->unique();

            // Karta raqami (masklangan holatda: 860012******1234)
            $table->string('number')->nullable();

            // Amal qilish muddati (MMYY)
            $table->string('expiry', 4)->nullable();

            // Karta egasining telefoni (agar kerak bo‘lsa)
            $table->string('phone', 20)->nullable();

            // Karta nomi (masalan: “My Uzcard”, “Salary Card”)
            $table->string('label')->nullable()->default('personal');

            // Default karta yoki yo‘qligi
            $table->boolean('is_default')->default(false);

            // Holat (active / blocked / expired va h.k.)
            $table->enum('status', ['active', 'blocked', 'expired','verified', 'not_verified'])->default('active');

            // Qo‘shimcha JSON maydon (bankdan qaytgan raw ma’lumotlar)
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
