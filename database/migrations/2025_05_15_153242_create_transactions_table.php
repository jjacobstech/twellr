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
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('buyer_id')->nullable()->index();
            $table->decimal('amount', 10);
            $table->string('transaction_type');
            $table->enum('status', ['failed', 'successful', 'pending', 'processing', 'approved', 'shipping', 'rejected', 'completed']);
            $table->timestamps();
            $table->string('ref_no');
            $table->float('charge')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
