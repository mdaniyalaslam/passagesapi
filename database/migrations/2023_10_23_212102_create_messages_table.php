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
            $table->unsignedBigInteger('chat_id')->nullable();
            $table->unsignedBigInteger('sender_id')->nullable();
            // $table->unsignedBigInteger('gift_id')->nullable();
            $table->text('message')->nullable();
            $table->dateTime('schedule_date')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_schedule')->default(false);
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            // $table->foreign('gift_id')->references('id')->on('gifts')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
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
