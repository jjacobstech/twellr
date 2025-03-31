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
            $table->unsignedInteger('buyer_id')->index();
            $table->unsignedInteger('product_id')->index();
            $table->string('delivery_status', 50);
            $table->string('phone_no', 200);
            $table->string('address', 200);
            $table->string('city', 150);
            $table->string('zip', 50);
            $table->timestamps();
            $table->timestamp('expired_at')->nullable()->index(indexName: 'expired_at');
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