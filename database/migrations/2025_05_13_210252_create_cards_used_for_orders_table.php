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
        Schema::create('cards_used_for_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
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
        Schema::dropIfExists('cards_user_for_orders');
    }
};
