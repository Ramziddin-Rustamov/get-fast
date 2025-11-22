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
        Schema::create('company_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_balance_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->unsignedBigInteger('trip_id')->nullable(); // booking/trip manbasi
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('type')->default('income'); // income/outcome
            $table->string('reason')->nullable();
            $table->string('currency', 3)->default('UZS');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_balance_transactions');
    }
};
