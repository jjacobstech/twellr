<?php
use Mary\Traits\Toast;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Notification;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.admin')] class extends Component {
    public function mount()
    {
        // $this->dispatchToast('success', 'Welcome to the dashboard!');
    }
};

?>

<div class="flex  gap-1 w-full mt-1 h-screen">
    <!-- Sidebar component -->
    <x-admin-sidebar />


    <div class="w-full p-4 bg-white shadow-sm overflow-y-scroll">
        <!-- Weekly Stats Section -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Weekly Performance</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-pink-500 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Avg Weekly Designer Signups</h3>
                    <div class="text-2xl font-bold mt-2">{{ $weeklySignups ?? '0' }}</div>
                    <div class="text-xs mt-1">+{{ $weeklySignupGrowth ?? '0' }}% from last week</div>
                </div>

                <div class="p-4 bg-pink-500 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Weekly Designs Uploaded</h3>
                    <div class="text-2xl font-bold mt-2">{{ $weeklyDesigns ?? '0' }}</div>
                    <div class="text-xs mt-1">+{{ $weeklyDesignsGrowth ?? '0' }}% from last week</div>
                </div>

                <div class="p-4 bg-pink-500 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Avg Weekly Designer Income</h3>
                    <div class="text-2xl font-bold mt-2">${{ $weeklyIncome ?? '0' }}</div>
                    <div class="text-xs mt-1">+{{ $weeklyIncomeGrowth ?? '0' }}% from last week</div>
                </div>

                <div class="p-4 bg-pink-500 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Weekly Shirts Purchased</h3>
                    <div class="text-2xl font-bold mt-2">{{ $weeklyPurchases ?? '0' }}</div>
                    <div class="text-xs mt-1">+{{ $weeklyPurchasesGrowth ?? '0' }}% from last week</div>
                </div>
            </div>
        </div>

        <!-- Monthly & Annual Stats Section -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Monthly & Annual Metrics</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <!-- Monthly & Annual Designer Signups -->
                <div class="p-4 bg-white border rounded-xl shadow">
                    <h3 class="font-semibold text-gray-700 mb-3">Designer Signups</h3>
                    <div class="flex justify-between">
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Monthly</div>
                            <div class="text-xl font-bold text-pink-600">{{ $monthlySignups ?? '0' }}</div>
                        </div>
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Annually</div>
                            <div class="text-xl font-bold text-pink-600">{{ $annualSignups ?? '0' }}</div>
                        </div>
                    </div>
                    {{-- <livewire:pages.admin.charts.signup-trend /> --}}
                </div>

                <!-- Monthly & Annual Designs Uploaded -->
                <div class="p-4 bg-white border rounded-xl shadow">
                    <h3 class="font-semibold text-gray-700 mb-3">Designs Uploaded</h3>
                    <div class="flex justify-between">
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Monthly</div>
                            <div class="text-xl font-bold text-pink-600">{{ $monthlyDesigns ?? '0' }}</div>
                        </div>
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Annually</div>
                            <div class="text-xl font-bold text-pink-600">{{ $annualDesigns ?? '0' }}</div>
                        </div>
                    </div>
                    {{-- <livewire:pages.admin.charts.designs-trend /> --}}
                </div>

                <!-- Monthly & Annual Designer Incomes -->
                <div class="p-4 bg-white border rounded-xl shadow">
                    <h3 class="font-semibold text-gray-700 mb-3">Designer Incomes</h3>
                    <div class="flex justify-between">
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Monthly</div>
                            <div class="text-xl font-bold text-pink-600">${{ $monthlyIncome ?? '0' }}</div>
                        </div>
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Annually</div>
                            <div class="text-xl font-bold text-pink-600">${{ $annualIncome ?? '0' }}</div>
                        </div>
                    </div>
                    {{-- <livewire:pages.admin.charts.income-trend /> --}}
                </div>

                <!-- Monthly & Annual Shirts Purchased -->
                <div class="p-4 bg-white border rounded-xl shadow">
                    <h3 class="font-semibold text-gray-700 mb-3">Shirts Purchased</h3>
                    <div class="flex justify-between">
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Monthly</div>
                            <div class="text-xl font-bold text-pink-600">{{ $monthlyPurchases ?? '0' }}</div>
                        </div>
                        <div class="text-center p-3 bg-pink-100 rounded-lg">
                            <div class="text-sm text-gray-600">Annually</div>
                            <div class="text-xl font-bold text-pink-600">{{ $annualPurchases ?? '0' }}</div>
                        </div>
                    </div>
                    {{-- <livewire:pages.admin.charts.purchases-trend /> --}}
                </div>
            </div>
        </div>

        <!-- All-Time Stats Section -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Lifetime Platform Statistics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-gray-800 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Total Designer Signups</h3>
                    <div class="text-3xl font-bold mt-2">{{ $totalDesignerSignups ?? '0' }}</div>
                </div>

                <div class="p-4 bg-gray-800 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Total User Signups</h3>
                    <div class="text-3xl font-bold mt-2">{{ $totalUserSignups ?? '0' }}</div>
                </div>

                <div class="p-4 bg-gray-800 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Total Designer Income</h3>
                    <div class="text-3xl font-bold mt-2">${{ $totalDesignerIncome ?? '0' }}</div>
                </div>

                <div class="p-4 bg-gray-800 text-white rounded-xl shadow">
                    <h3 class="font-semibold text-sm uppercase">Total Shirts Purchased</h3>
                    <div class="text-3xl font-bold mt-2">{{ $totalShirtsPurchased ?? '0' }}</div>
                </div>
            </div>
        </div>

        <!-- Main Chart Section -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Performance Overview</h2>
            <div class="bg-white p-4 rounded-xl border shadow">
                <livewire:pages.admin.charts.main-overview />
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-8 border-t pt-4">
            <p class="text-center text-gray-600 text-sm">Copyright © 2025 Twellr. All Rights Reserved</p>
        </footer>
    </div>
</div>
