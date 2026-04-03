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
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ismi
            $table->string('email'); // Elektron pochta
            $table->text('message'); // Xabari
            // Status (admin javob berdimi yo'qmi)
            $table->enum('status', ['pending', 'answered', 'closed'])->default('pending');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
