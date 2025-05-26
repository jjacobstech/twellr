<?php
use App\Models\Purchase;
use App\Models\AdminSetting;
use function Livewire\Volt\{layout, mount, state};

layout('layouts.app');
mount(function () {
    session()->forget('user');
    session()->forget('secret');
});
state(['purchases' => fn() => Auth::user()->isCreative() ? Purchase::where('buyer_id', '=', Auth::id())->latest()->with('product')->take(5)->get() : Purchase::where('buyer_id', '=', Auth::id())->latest()->with('product')->take(6)->get()]);
state(['banner' => fn() => AdminSetting::first()->banner_image]);

?>
<div class="flex h-screen mb-20 overflow-hidden md:w-full" x-cloak="display: none">
    @if (Auth::user()->isCreative())
        <x-creative-sidebar class="w-[12%]" />
    @endif
    @if (route('dashboard') == url()->current())
        <div
            class="w-full overflow-y-scroll px-3 py-3 pb-40 md:pb-20 scrollbar-none space-y-5 bg-white md:pt-5 md:flex md:flex-col md:flex-1 md:h-full md:w-[82%] md:overflow-auto lg:mx-1">
            <!-- Banner Image -->
            <div class="relative">
                <img loading="lazy" class="w-full rounded-xl h-[200px] md:h-[260px] object-cover"
                    src="{{ asset('uploads/banner/' . $banner) }}" alt="Dashboard banner">
            </div>

            <!-- Products Section -->
            <div class="w-full scrollbar-none">
                @if (count($purchases) > 0)
                    @if (Auth::user()->isCreative())
                        <!-- Mobile/Tablet View for Creative Users -->
                        <div class="grid w-full gap-3 sm:grid-cols-2 scrollbar-none md:grid-cols-3 lg:hidden">
                            @foreach ($purchases as $purchase)
                                <x-dashboard-product-card :$purchase />
                            @endforeach
                        </div>

                        <!-- Desktop View for Creative Users -->
                        <div class="hidden w-full gap-3 lg:grid lg:grid-cols-5">
                            @foreach ($purchases as $purchase)
                                <x-dashboard-product-card :$purchase />
                            @endforeach
                        </div>
                    @else
                        <!-- Mobile/Tablet View for Non-Creative Users -->
                        <div class="grid w-full gap-4 sm:grid-cols-4 scrollbar-none md:grid-cols-6 lg:hidden">
                            @foreach ($purchases as $purchase)
                                <x-dashboard-product-card :$purchase />
                            @endforeach
                        </div>

                        <!-- Desktop View for Non-Creative Users -->
                        <div class="hidden w-full gap-4 lg:grid lg:grid-cols-6">
                            @foreach ($purchases as $purchase)
                                <x-dashboard-product-card :$purchase />
                            @endforeach
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                        <!-- Icon -->
                        <div class="mb-6">
                            <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 9l3-3 3 3"></path>
                            </svg>
                        </div>

                        <!-- Title -->
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            No products yet
                        </h3>

                        <!-- Description -->
                        <p class="text-gray-500 mb-8 max-w-md">
                            @if (Auth::user()->isCreative())
                                Start building your creative portfolio by adding your first product. Share your work with the world and begin your creative journey.
                            @else
                                Discover amazing products from talented creators. Browse the marketplace to find something that inspires you.
                            @endif
                        </p>

                        <!-- Action Button -->
                        @if (Auth::user()->isCreative())
                            <a href="{{ route('creative.upload') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Your First Product
                            </a>
                        @else
                            <a href="{{ route('market.place') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Browse Marketplace
                            </a>
                        @endif

                        <!-- Secondary Action (Optional) -->
                        <div class="mt-4">

                                <a href="{{ route('support') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                    Need help getting started? View our guide →
                                </a>
                        
                        </div>
                    </div>
                @endif
            </div>

        </div>
    @endif
</div>
