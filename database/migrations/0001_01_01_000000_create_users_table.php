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
            $table->string('name');
            $table->text('avatar')->nullable();
            $table->text('cover')->nullable();
            $table->text('address')->nullable();
            $table->string('phone_no');
            $table->string('email')->unique();
            $table->string('google_id')->nullable();
            $table->enum('role', ['user', 'creative', 'admin']);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->decimal('price', 10);
            $table->decimal('wallet', 10);
            $table->decimal('balance', 10);
            $table->string('bank');
            $table->string('bank_account_name');
            $table->string('bank_account_no');
            $table->enum('notify_purchase', ['yes', 'no'])->default('yes');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};