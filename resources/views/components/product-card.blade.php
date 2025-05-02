<div class="max-w-sm overflow-hidden transition-shadow duration-300 bg-white shadow-md rounded-xl hover:shadow-lg">
    <!-- Product Image -->
    <div class="relative group">
        <img class="object-cover w-full h-64 rounded-t-xl"
            src="{{ asset('uploads/products/design-stack/' . $product->front_view) }}" alt="{{ $product->name }}">
        <!-- Quick view overlay - appears on hover -->
        <div
            class="absolute inset-0 flex items-center justify-center transition-opacity duration-300 bg-black opacity-0 bg-opacity-40 group-hover:opacity-100 rounded-t-xl">
            <!-- Action Buttons -->
            @if ($product->user_id != Auth::id() && Auth::user()->role != "admin")
                <div class="hidden px-5 pb-4 mb-20 space-x-2 lg:flex">
                    <x-bladewind.button wire:click="orderModal({{ $product->id }})" size="small" radius="medium"
                        type="bg-navy-blue hover:bg-golden focus:ring-0" button_text_css="text-white" class="flex-1">
                        Buy Now
                    </x-bladewind.button>

                    <button wire:click="addToCart({{ $product->id }})"
                        class="hidden p-2 transition-colors border border-gray-300 rounded-md hover:border-golden hover:bg-golden lg:block">
                        <span>
                            @svg('eva-shopping-cart-outline', 'w-6 h-5 text-white')
                        </span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Price tag overlay - always visible -->
        <div class="absolute px-3 py-1 text-white bg-black rounded-full top-2 right-2 bg-opacity-80">
            <span
                class="font-bold text-golden">{{ App\Models\AdminSetting::first()->value('currency_symbol') . $product->price }}</span>
        </div>
    </div>

    <!-- Product Info -->
    <div class="px-5 py-4">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <!-- Larger, more prominent product name -->
                <h3 class="mb-1 text-xl font-bold text-gray-800">{{ $product->name }}</h3>

                <!-- Secondary price display -->
                <div class="flex items-center mb-2">
                    <span class="mr-2 font-bold text-gray-600 text-md">
                        @if ($product->status == 'available')
                            In Stock
                        @else
                            Out Of Stock
                        @endif
                    </span>

                </div>
            </div>

            @if ($product->user_id != Auth::id() && Auth::user()->role != "admin")
                <div class="flex gap-2">
                    <button wire:click="addToCart({{ $product->id }})" class="p-2 transition-colors border rounded-md">
                        <span>
                            @svg('eva-shopping-cart-outline', 'w-6 h-5 text-navy-blue fill-navy-blue')
                        </span>
                    </button>
                </div>
            @endif

        </div>

        <!-- Category or product type badge -->
        <div class="flex mt-2 mb-3 ">
            <div class=" w-[70%]">
                <span
                    class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-md ">{{ $product->category }}</span>
            </div>
            @if ($product->user_id != Auth::id() && Auth::user()->role != "admin")
                <div class="w-[30%]">
                    <x-bladewind.button wire:click="orderModal({{ $product->id }})" size="tiny" radius="small"
                        type="bg-navy-blue hover:bg-golden focus:ring-0" button_text_css="text-white" class="flex-1 ">
                        Buy Now
                    </x-bladewind.button>
                </div>
            @endif
        </div>
        <!-- Action Buttons -->

        <!-- Brief description if available -->
        @if (isset($product->description))
            <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $product->description }}</p>
        @endif
    </div>


</div>
