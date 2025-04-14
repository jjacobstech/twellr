<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminSetting>
 */
class AdminSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
     return    [
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
     ];
    }
}
