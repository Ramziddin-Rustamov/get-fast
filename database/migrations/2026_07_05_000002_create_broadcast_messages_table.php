<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin tomonidan yuboriladigan ommaviy e'lonlar (broadcast push).
     */
    public function up(): void
    {
        Schema::create('broadcast_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();               // Sarlavha
            $table->text('body');                              // Xabar matni
            $table->enum('audience', ['all', 'driver', 'client'])->default('all'); // Kimga
            $table->foreignId('sender_id')->nullable()         // Qaysi admin yubordi
                ->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'sending', 'sent', 'failed'])->default('pending');
            $table->unsignedInteger('recipients_count')->default(0); // Nechta tokenga jo'natildi
            $table->unsignedInteger('sent_count')->default(0);       // Nechtasi muvaffaqiyatli
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_messages');
    }
};
