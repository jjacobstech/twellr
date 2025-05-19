<?php
use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\AdminSetting;
use App\Models\PlatformEarning;
use function Livewire\Volt\{state, layout};

layout('layouts.admin');

state([
    //Currency
    'currency' => fn() => AdminSetting::first()->currency_symbol,

    // Weekly stats
    'weeklySignups' => fn() => User::where('role', 'user')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
    'averageWeeklyDesignerSignups' => fn() =>collect(range(1, 4)) // last 4 weeks
    ->map(function ($weekAgo) {
        $start = Carbon::now()->subWeeks($weekAgo)->startOfWeek();
        $end = Carbon::now()->subWeeks($weekAgo)->endOfWeek();

        return User::where('role', 'creative')
            ->whereBetween('created_at', [$start, $end])
            ->count();
    })
    ->average(), //fix
    'weeklyDesigns' => fn() => Product::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
    'weeklyIncome' => fn() => Transaction::where('transaction_type', '=', 'sales')
        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
        ->sum('amount'),
    'weeklyPurchases' => fn() => Purchase::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('quantity'),
    'averageWeeklyDesignerIncome' => fn() => collect(range(1, 4)) // last 4 weeks
    ->map(function ($weekAgo) {
        $start = Carbon::now()->subWeeks($weekAgo)->startOfWeek();
        $end = Carbon::now()->subWeeks($weekAgo)->endOfWeek();

        return Transaction::where('transaction_type', 'sales')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
    })
    ->average(),

    // Monthly and Annual stats
    'monthlyDesignerSignups' => fn() => User::where('role', '=', 'creative')
        ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
        ->count(),
    'annualDesignerSignups' => fn() => User::where('role', '=', 'creative')
        ->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
        ->count(),
    'monthlyDesigns' => fn() => Product::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count(),
    'annualDesigns' => fn() => Product::whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->count(),
    'monthlyIncome' => fn() => Transaction::where('transaction_type', '=', 'sales')
        ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
        ->sum('amount'),
    'annualIncome' => fn() => Transaction::where('transaction_type', '=', 'sales')
        ->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
        ->sum('amount'),
    'monthlyPurchases' => fn() => Purchase::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('quantity'),
    'annualPurchases' => fn() => Purchase::whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->sum('quantity'),

    //Platform Earnings
    'weeklyEarnings' => fn() => PlatformEarning::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total'),
    'monthlyEarnings' => fn() => PlatformEarning::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('total'),
    'annualEarnings' => fn() => PlatformEarning::whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->sum('total'),
    'totalEarnings' => fn() => PlatformEarning::sum('total'),


    // All-time stats
    'totalDesignerSignups' => fn() => User::where('role', '=', 'creative')->count(),
    'totalUserSignups' => fn() => User::where('role', '=', 'user')->count(),
    'totalDesignerIncome' => fn() => Transaction::where('transaction_type', '=', 'sales')->sum('amount'),
    'totalShirtsPurchased' => fn() => Purchase::sum('quantity'),
]);

?>

<div class="fixed flex w-full h-screen gap-1 pt-1 overflow-hidden bg-gray-100">
    <!-- Sidebar component -->
    <x-admin-sidebar />


    <div
        class="w-full p-4 mb-16 overflow-y-scroll bg-white shadow-sm scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
        <!-- Weekly Stats Section -->
        <div class="mb-6">
            <h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b">Weekly Performance</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="p-4 text-white shadow bg-navy-blue rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Weekly User Signups</h3>
                    <div class="mt-2 text-2xl font-bold">{{ round($weeklySignups) ?? '0' }}</div>
                </div>
                 <div class="p-4 text-white shadow bg-navy-blue rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Average Weekly Designer Signups</h3>
                    <div class="mt-2 text-2xl font-bold">{{ round($averageWeeklyDesignerSignups) ?? '0' }}</div>
                </div>

                <div class="p-4 text-white shadow bg-navy-blue rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Weekly Designs Uploaded</h3>
                    <div class="mt-2 text-2xl font-bold">{{ round($weeklyDesigns) ?? '0' }}</div>
                </div>

                <div class="p-4 text-white shadow bg-navy-blue rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Average Weekly Designer Income</h3>
                    <div class="mt-2 text-2xl font-bold">{{ $currency }}{{ $averageWeeklyDesignerIncome ?? '0' }}</div>
                </div>

                <div class="p-4 text-white shadow bg-navy-blue rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Weekly Shirts Purchased</h3>
                    <div class="mt-2 text-2xl font-bold">{{ $weeklyPurchases ?? '0' }}</div>
                </div>
            </div>
        </div>

        <!-- Monthly & Annual Stats Section -->
        <div class="mb-6">
            <h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b">Monthly & Annual Metrics</h2>

            <div class="grid grid-cols-1 gap-4 mb-4 lg:grid-cols-2">
                <!-- Monthly & Annual Designer Signups -->
                <div class="p-4 bg-white border shadow rounded-xl">
                    <h3 class="mb-3 font-semibold text-gray-700">Designer Signups</h3>
                    <div class="flex justify-between">
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Monthly</div>
                            <div class="text-xl font-bold text-white">{{ $monthlyDesignerSignups ?? '0' }}</div>
                        </div>
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Annually</div>
                            <div class="text-xl font-bold text-white">{{ $annualDesignerSignups ?? '0' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Monthly & Annual Designs Uploaded -->
                <div class="p-4 bg-white border shadow rounded-xl">
                    <h3 class="mb-3 font-semibold text-gray-700">Designs Uploaded</h3>
                    <div class="flex justify-between">
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Monthly</div>
                            <div class="text-xl font-bold text-white">{{ $monthlyDesigns ?? '0' }}</div>
                        </div>
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Annually</div>
                            <div class="text-xl font-bold text-white">{{ $annualDesigns ?? '0' }}</div>
                        </div>
                    </div>
                    {{-- <livewire:pages.admin.charts.designs-trend /> --}}
                </div>

                <!-- Monthly & Annual Designer Incomes -->
                <div class="p-4 bg-white border shadow rounded-xl">
                    <h3 class="mb-3 font-semibold text-gray-700">Designer Incomes</h3>
                    <div class="flex justify-between">
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Monthly</div>
                            <div class="text-xl font-bold text-white">{{ $currency }}{{ $monthlyIncome ?? '0' }}
                            </div>
                        </div>
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Annually</div>
                            <div class="text-xl font-bold text-white">{{ $currency }}{{ $annualIncome ?? '0' }}
                            </div>
                        </div>
                    </div>
                    {{-- <livewire:pages.admin.charts.income-trend /> --}}
                </div>

                <!-- Monthly & Annual Shirts Purchased -->
                <div class="p-4 bg-white border shadow rounded-xl">
                    <h3 class="mb-3 font-semibold text-gray-700">Shirts Purchased</h3>
                    <div class="flex justify-between">
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Monthly</div>
                            <div class="text-xl font-bold text-white">{{ $monthlyPurchases ?? '0' }}</div>
                        </div>
                        <div class="p-3 text-center rounded-lg bg-navy-blue">
                            <div class="text-sm text-white">Annually</div>
                            <div class="text-xl font-bold text-white">{{ $annualPurchases ?? '0' }}</div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

                <!-- Platform Earnings - Monthly & Annual -->

        <div class="mb-6">
            <h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b">Platform Earning</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Weekly Earnings</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $currency }}{{ $weeklyEarnings ?? '0' }}</div>
                </div>

                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Month Earnings</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $currency }}{{ $monthlyEarnings ?? '0' }}</div>
                </div>

                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Yearly Earnings</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $currency }}{{ $annualEarnings ?? '0' }}</div>
                </div>

                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Total Earnings</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $currency }}{{ $totalEarnings ?? '0' }}</div>
                </div>
            </div>
        </div>

        <!-- All-Time Stats Section -->
        <div class="mb-6">
            <h2 class="pb-2 mb-4 text-xl font-bold text-gray-800 border-b">Lifetime Platform Statistics</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Total Designer Signups</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $totalDesignerSignups ?? '0' }}</div>
                </div>

                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Total User Signups</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $totalUserSignups ?? '0' }}</div>
                </div>

                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Total Designer Income</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $currency }}{{ $totalDesignerIncome ?? '0' }}</div>
                </div>

                <div class="p-4 text-white bg-gray-800 shadow rounded-xl">
                    <h3 class="text-sm font-semibold uppercase">Total Shirts Purchased</h3>
                    <div class="mt-2 text-3xl font-bold">{{ $totalShirtsPurchased ?? '0' }}</div>
                </div>
            </div>
        </div>



        <!-- Footer -->
        <footer class="pt-4 mt-8 border-t">
            <p class="text-sm text-center text-gray-500">Copyright © 2025 Twellr. All Rights Reserved</p>
        </footer>
    </div>
</div>
