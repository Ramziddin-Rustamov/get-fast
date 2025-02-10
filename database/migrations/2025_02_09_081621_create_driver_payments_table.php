<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('driver_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade'); // Admin ID
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade'); // Haydovchi ID
            $table->decimal('amount', 12, 2); // To'langan summa
            $table->timestamp('transaction_date')->useCurrent(); // To'lov sanasi
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_payments');
    }
};
