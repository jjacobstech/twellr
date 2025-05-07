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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['country_id'])->references(['id'])->on('countries')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['referred_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['state_id'])->references(['id'])->on('states')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_country_id_foreign');
            $table->dropForeign('users_referred_by_foreign');
            $table->dropForeign('users_state_id_foreign');
        });
    }
};
