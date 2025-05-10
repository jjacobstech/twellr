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
            $table->bigIncrements('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->text('avatar')->nullable();
            $table->text('cover')->nullable();
            $table->text('address')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('email')->unique();
            $table->string('google_id')->nullable();
            $table->enum('role', ['user', 'creative', 'admin']);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('facebook')->nullable();
            $table->string('x')->nullable();
            $table->string('instagram')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('password');
            $table->decimal('rating', 10)->default(0);
            $table->string('referral_link')->nullable()->unique();
            $table->integer('referral_rewards')->default(0);
            $table->unsignedBigInteger('referred_by')->nullable()->index('users_referred_by_foreign');
            $table->decimal('wallet_balance', 10)->nullable()->default(0);
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_no')->nullable();
            $table->enum('notify_purchase', ['yes', 'no'])->default('yes');
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedBigInteger('country_id')->nullable()->index('users_country_id_foreign');
            $table->unsignedBigInteger('state_id')->nullable()->index('users_state_id_foreign');
            $table->float('discount')->unsigned()->nullable()->default(0);
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
