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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // sms qaysi maqsadda yuborilayotgani yoziladi. Masalan: register, reset password
            $table->string('phone'); // yuborilayotgan smsning tel nomeri saqlanadi
            $table->text('content'); // yuborilayotgan sms xabari saqlanadi.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms');
    }
};
