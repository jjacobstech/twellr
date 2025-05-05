    @php
    use App\Models\Product;
    auth()->user()->role == 'creative' ? ($latestProducts = Product::latest()->take(5)->get()) : ($latestProducts = Product::latest()->take(6)->get());
    @endphp
    <x-app-layout>

        <div class="flex h-screen m-0 overflow-hidden md:w-full">
            @if (Auth::user()->isCreative())
                <x-creative-sidebar class="w-[12%]" />
            @endif
            @if (route('dashboard') == url()->current())
                <div
                    class="w-full overflow-y-scroll px-3 py-3 pb-20 scrollbar-none space-y-5 bg-white md:pt-5 md:flex md:flex-col md:flex-1 md:h-full md:w-[82%] md:overflow-auto lg:mx-1">
                    <!-- Banner Image -->
                    <div class="relative">
                        <img class="w-full rounded-xl h-[200px] md:h-[254px] object-cover"
                            src="{{ asset('assets/sales.png') }}" alt="Dashboard banner">
                    </div>

                    <!-- Products Section -->
                    <div class="w-full scrollbar-none">
                        @if (Auth::user()->isCreative())
                            <!-- Mobile/Tablet View for Creative Users -->
                            <div class="grid w-full gap-3 sm:grid-cols-2 scrollbar-none md:grid-cols-3 lg:hidden">
                                @foreach ($latestProducts as $latestProduct)
                                    <x-dashboard-product-card :product="$latestProduct" />
                                @endforeach
                            </div>

                            <!-- Desktop View for Creative Users -->
                            <div class="hidden w-full gap-3 lg:grid lg:grid-cols-5">
                                @foreach ($latestProducts as $latestProduct)
                                    <x-dashboard-product-card :product="$latestProduct" />
                                @endforeach
                            </div>
                        @else
                            <!-- Mobile/Tablet View for Non-Creative Users -->
                            <div class="grid w-full gap-4 sm:grid-cols-4 scrollbar-none md:grid-cols-6 lg:hidden">
                                @foreach ($latestProducts as $latestProduct)
                                    <x-dashboard-product-card :product="$latestProduct" />
                                @endforeach
                            </div>

                            <!-- Desktop View for Non-Creative Users -->
                            <div class="hidden w-full gap-4 lg:grid lg:grid-cols-6">
                                @foreach ($latestProducts as $latestProduct)
                                    <x-dashboard-product-card :product="$latestProduct" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </x-app-layout>
