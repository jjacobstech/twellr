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
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('transactions_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('products_id')->index();
            $table->string('delivery_status', 50)->default('delivered');
            $table->longText('description_custom_content')->nullable();
            $table->timestamps();
            $table->string('address', 200)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('zip', 50)->nullable();
            $table->char('phone', 20)->nullable();
            $table->timestamp('expired_at')->nullable()->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
