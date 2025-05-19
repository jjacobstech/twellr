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
            $table->float('amount')->default(0);
            $table->integer('location_id');
            $table->string('size', 50)->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->string('product_name');
            $table->string('product_category');
            $table->string('material_id');
            $table->integer('quantity');
            $table->integer('discounted')->nullable();
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
