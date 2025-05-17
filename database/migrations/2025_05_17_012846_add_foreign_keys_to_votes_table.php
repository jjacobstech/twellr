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
        Schema::table('votes', function (Blueprint $table) {
            $table->foreign(['contestant_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign('votes_contestant_id_foreign');
            $table->dropForeign('votes_product_id_foreign');
            $table->dropForeign('votes_user_id_foreign');
        });
    }
};
