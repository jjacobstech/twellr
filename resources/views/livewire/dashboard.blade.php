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
            </div>

        </div>
    @endif
</div>
