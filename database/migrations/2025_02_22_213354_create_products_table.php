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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('name');
            $table->char('type', 50);
            $table->decimal('price', 10);
            $table->unsignedInteger('delivery_time');
            $table->text('description');
            $table->string(column: 'designfile');
            $table->string(column: 'designimage');
            $table->string('mime', 50)->nullable();
            $table->string('extension', 50)->nullable();
            $table->string('size', 50)->nullable();
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};