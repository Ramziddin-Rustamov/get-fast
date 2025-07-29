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
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('balance', 12, 2)->default(0); // Asosiy balans
            $table->decimal('after_taxes', 12, 2)->default(0); // Asosiy balans
            $table->decimal('tax')->default(0);
            $table->decimal('locked_balance', 12, 2)->default(0); // Hozircha foydalanuvchi ishlata olmaydigan summa (masalan: rezerv qilingan)
            $table->string('currency')->default('UZS'); // optional: USD, EUR, UZS
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_balances');
    }
};
