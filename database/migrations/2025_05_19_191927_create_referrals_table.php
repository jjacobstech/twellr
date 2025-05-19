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
        Schema::create('referrals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_id')->index('referrals_referred_id_foreign');
            $table->string('code_used');
            $table->enum('status', ['pending', 'converted', 'expired'])->default('pending');
            $table->integer('reward_points')->default(0);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->unique(['referrer_id', 'referred_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
