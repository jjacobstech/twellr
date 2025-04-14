<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function seed(){
        
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->char('currency_symbol', 10)->nullable();
            $table->string('currency_code', 20)->nullable();
            $table->unsignedInteger('commission_fee')->default(0);
            $table->unsignedInteger('shipping_fee')->default(0);
            $table->string('logo', 100)->nullable();
            $table->string('logo_2', length: 100)->nullable();
            $table->string('text_logo', length: 100)->nullable();
            $table->string('favicon', 100)->nullable();
            $table->enum('maintenance_mode', ['on', 'off'])->default('off');
            $table->unsignedInteger('vat')->default(0);
            $table->boolean('advertisement_status')->default(true);
        });
        $this->seed('AdminSettingsSeeder');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
