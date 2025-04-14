<?php

namespace Database\Seeders;

use App\Models\AdminSetting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        AdminSetting::factory()->create([
            'currency_symbol' => '₦',
            'currency_code' => 'NGN',
            'commission_fee' => 0,
            'shipping_fee' => 0,
            'logo' => 'logo.png',
            'logo_2' => 'logo_2.png',
            'text_logo' => 'text_logo.png',
            'favicon' => 'favicon.png',
            'maintenance_mode' => 'off',
            'vat' => 0,
            'advertisement_status' => true,
        ]);
    }
}
