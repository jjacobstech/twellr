<?php

use App\Models\AdminSetting;
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
            $table->char('currency_symbol', 10)->nullable();
            $table->string('currency_code', 20)->nullable();
            $table->decimal('commission_fee', 10)->unsigned()->nullable();
            $table->string('withdrawal_time', 100)->nullable();
            $table->unsignedInteger('withdrawal_threshold')->nullable();
            $table->unsignedInteger('minimum_rating')->nullable();
            $table->string('banner_image')->nullable();
            $table->integer('voting')->nullable();
        });

        AdminSetting::create([
            'currency_symbol' => '₦',
            'currency_code' => 'NGN',
            'commission_fee' => 5,
            'withdrawal_time' => 24,
            'withdrawal_threshold' => 10000,
            'minimum_rating' => 10,
            'banner_image' => 'banner.png',
            'voting' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
