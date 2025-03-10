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
            $table->string('category');
            $table->text('description');
            $table->string(column: 'print_stack');
            $table->string('print_stack_mime', 50)->nullable();
            $table->string('print_stack_extension', 50)->nullable();
            $table->string('print_stack_size', 50)->nullable();
            $table->string('design_stack');
            $table->string('design_stack_mime', 50)->nullable();
            $table->string('design_stack_extension', 50)->nullable();
            $table->string('size', 50)->nullable();
            $table->enum('status', ['available', 'unavailable'])->default('available');
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