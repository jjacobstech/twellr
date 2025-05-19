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
            $table->char('currency_symbol', 10)->nullable()->default('₦');
            $table->string('currency_code', 20)->nullable()->default('NGN');
            $table->decimal('commission_fee', 10)->unsigned()->default(5);
            $table->string('withdrawal_time', 100)->nullable()->default('24');
            $table->enum('maintenance_mode', ['on', 'off'])->default('off');
            $table->unsignedInteger('withdrawal_threshold')->nullable()->default(10000);
            $table->unsignedInteger('minimum_rating')->default(0);
            $table->string('banner_image')->nullable()->default('banner.png');
            $table->integer('voting')->default(1);
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
