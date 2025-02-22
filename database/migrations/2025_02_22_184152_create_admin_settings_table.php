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
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description');
            $table->enum('email_verification', ['0', '1'])->comment('0 Off, 1 On');
            $table->string('email_no_reply', 200);
            $table->string('email_admin', 200);
            $table->char('currency_symbol', 10);
            $table->string('currency_code', 20);
            $table->unsignedInteger('fee_commission');
            $table->string('currency_position', 100)->default('left');
            $table->unsignedInteger('days_process_withdrawals');
            $table->enum('google_login', ['on', 'off'])->default('off');
            $table->string('logo', 100);
            $table->string('logo_2', length: 100);
            $table->string('text_logo', length: 100);
            $table->string('favicon', 100);
            $table->string('avatar', 100);
            $table->enum('maintenance_mode', ['on', 'off'])->default('off');
            $table->string('vat', length: 100);
            $table->enum('wallet_format', ['real_money', 'credits', 'points', 'tokens'])->default('real_money');
            $table->boolean('advertisement_status')->default(true);
            $table->enum('referral_system', ['on', 'off'])->default('off');
            $table->boolean('push_notification_status')->default(false);
            $table->string('onesignal_appid', 150);
            $table->string('onesignal_restapi', 150);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
