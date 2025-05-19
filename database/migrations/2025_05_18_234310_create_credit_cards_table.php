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
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('user_type', ['client', 'driver'])->default('client');
            $table->string('card_number')->nullable(); // Masked card number
            $table->string('expiry_month')->nullable();      // Expiry date
            $table->string('expiry_year')->nullable();
            $table->string('token')->nullable(); // Payme token
            $table->boolean('is_active')->default(true);
            $table->boolean('cvv')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
