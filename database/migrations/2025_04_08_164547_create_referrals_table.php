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
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->string('code_used');
            $table->enum('status', ['pending', 'converted', 'expired'])->default('pending');
            $table->integer('reward_points')->default(0);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            // Prevent duplicate referrals
            $table->unique(['referrer_id', 'referred_id']);
        });

        // Add referral-related columns to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referral_link')) {
                $table->string('referral_link')->unique()->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'referral_rewards')) {
                $table->integer('referral_rewards')->default(0)->after('referral_link');
            }

            if (!Schema::hasColumn('users', 'referred_by')) {
                $table->foreignId('referred_by')->nullable()->after('referral_rewards')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');

        // Remove added columns from users table
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referral_link')) {
                $table->dropColumn('referral_link');
            }

            if (Schema::hasColumn('users', 'referral_rewards')) {
                $table->dropColumn('referral_rewards');
            }

            if (Schema::hasColumn('users', 'referred_by')) {
                $table->dropForeign(['referred_by']);
                $table->dropColumn('referred_by');
            }
        });
    }
};