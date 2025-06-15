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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('pay_id')->unique(); // bankdan qaytgan transaction ID
            $table->string('external_id')->nullable();
            $table->unsignedBigInteger('card_id'); // foreign key
            $table->integer('amount');
            $table->string('currency_code')->default('860');
            $table->enum('status', ['created', 'held', 'confirmed', 'canceled', 'returned'])->default('created');
            $table->json('payer_data')->nullable();
            $table->json('receiver_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
