<div
    class="w-full  max-w-sm md:overflow-hidden transition-shadow duration-300 shadow-md rounded-xl hover:shadow-lg aspect-square">
    <!-- Product Image Container with fixed aspect ratio -->
    <div class="relative group aspect-square">
        <img class="object-cover aspect-square w-full h-full rounded-t-xl lg:rounded-t-none"
            src="{{ asset('uploads/products/design-stack/' . $product->front_view) }}" alt="{{ $product->name }}">

        <!-- Quick view overlay - appears on hover (desktop only) -->
        <div
            class="absolute inset-0 hidden lg:flex items-center justify-center transition-opacity duration-300 bg-black opacity-0 bg-opacity-40 group-hover:opacity-100 rounded-t-xl lg:rounded-t-none">
            <!-- Action Buttons for Desktop -->
            @if ($product->user_id != Auth::id() && Auth::user()->role != 'admin')
                <div class="flex px-5 pb-4 space-x-2">
                    <x-bladewind.button wire:click="orderModal({{ $product->id }})" size="small" radius="medium"
                        type="bg-navy-blue hover:bg-golden focus:ring-0" button_text_css="text-white" class="flex-1">
                        Buy Now
                    </x-bladewind.button>

                    <button wire:click="addToCart({{ $product->id }})"
                        class="p-2 transition-colors border border-gray-300 rounded-md hover:border-golden hover:bg-golden">
                        <span>
                            @svg('eva-shopping-cart-outline', 'w-5 h-5 text-white')
                        </span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Price tag overlay - always visible -->
        <div class="absolute px-2 py-1 sm:px-3 text-white bg-black rounded-full top-2 right-2 bg-opacity-80">
            <span class="text-xs sm:text-sm md:text-base font-bold text-golden">
                {{ App\Models\AdminSetting::first()->value('currency_symbol') . $product->price }}
            </span>
        </div>
    </div>

    {{-- <!-- Product Info - Always visible on mobile/tablet, customized for desktop --> --}}
    <div class="px-3 py-3 sm:px-4 sm:py-4 lg:px-5 lg:hidden">
        <!-- Mobile & Tablet View -->
        <div class="lg:hidden">
            <div class="flex justify-between">
                <div class="grid">
                    <!-- Product name with truncation for long names -->
                    <h3 class="mb-1 text-lg sm:text-xl font-bold text-gray-800 truncate w-20">{{ $product->name }}</h3>

                    <!-- Stock status -->
                    <div class="flex items-center mb-2">
                        <span class="text-xs sm:text-sm font-bold text-gray-600">
                            @if ($product->status == 'available')
                                In Stock
                            @else
                                Out Of Stock
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Cart button - Mobile & Tablet Only -->
                @if ($product->user_id != Auth::id() && Auth::user()->role != 'admin')
                    <div class="flex-shrink-0">
                        <button wire:click="addToCart({{ $product->id }})"
                            class="p-1.5 sm:p-2 transition-colors border rounded-md hover:bg-gray-100">
                            <span>
                                @svg('eva-shopping-cart-outline', 'w-5 h-5 text-navy-blue fill-navy-blue')
                            </span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Category and Buy Now button -->
            <div class="flex items-center justify-between mt-2 mb-2 gap-1">

                @if ($product->user_id != Auth::id() && Auth::user()->role != 'admin')
                    <div class="flex-shrink-0">
                        <x-bladewind.button wire:click="orderModal({{ $product->id }})" size="tiny" radius="small"
                            type="bg-navy-blue hover:bg-golden focus:ring-0 px-1" button_text_css="text-white"
                            class="md:w-full">
                            Buy Now
                        </x-bladewind.button>
                    </div>
                    @else
                    <div class="flex-shrink-0">
                        <x-bladewind.button disabled wire:click="orderModal({{ $product->id }})" size="tiny" radius="small"
                            type="bg-navy-blue hover:bg-golden focus:ring-0 px-1" button_text_css="text-white"
                            class="md:w-full">
                            Buy Now
                        </x-bladewind.button>
                    </div>
                @endif
            </div>

            <!-- Brief description if available -->
            @if (isset($product->description))
                <p class="mt-1 text-xs sm:text-sm text-gray-600 line-clamp-2 h-10 truncate">{{ $product->description }}
                </p>
            @endif
        </div>

        <!-- Desktop View - Minimalist approach -->
        <div class="hidden lg:block">
            <h3 class="text-lg font-bold text-gray-800 truncate">{{ $product->name }}</h3>
            <div class="flex items-center justify-between mt-2">
                <span class="inline-block px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-md">
                    {{ $product->category->name }}
                </span>
                <span class="text-xs font-bold text-gray-600">
                    @if ($product->status == 'available')
                        In Stock
                    @else
                        Out Of Stock
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
