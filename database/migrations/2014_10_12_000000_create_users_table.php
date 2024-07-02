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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('accountId')->nullable();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('account_type', ['facebook', 'google','apple'])->nullable();
            $table->string('account_id')->nullable();
            $table->string('family_name')->nullable();
            $table->string('given_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->dateTime('dob')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_privacy_policy')->default(false);
            $table->string('image')->nullable();
            $table->string('device_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
