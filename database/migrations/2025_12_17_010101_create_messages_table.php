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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('chat_id')->constrained()->cascadeOnDelete();
            $table->enum('sender_type', ['user', 'support']);
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->boolean('is_read_by_user')->default(false);
            $table->boolean('is_read_by_support')->default(false);
            $table->timestamps();
            $table->index(['chat_id', 'sender_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
