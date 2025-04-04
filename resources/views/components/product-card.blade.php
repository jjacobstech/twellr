<div class="max-w-sm overflow-hidden bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
    <!-- Product Image -->
    <div class="relative group">
        <img class="w-full h-64 object-cover rounded-t-xl"
            src="{{ asset('uploads/products/design-stack/' . $product->front_view) }}" alt="{{ $product->name }}">
        <!-- Quick view overlay - appears on hover -->
        <div
            class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-t-xl">
            <!-- Action Buttons -->
            <div class="px-5 pb-4  space-x-2 hidden lg:flex">
                <x-bladewind.button wire:click="orderModal({{ $product->id }})" size="small" radius="medium"
                    type="bg-navy-blue hover:bg-golden focus:ring-0" button_text_css="text-white" class="flex-1">
                    Buy Now
                </x-bladewind.button>

                <button wire:click="addToCart({{ $product->id }})"
                    class="p-2 rounded-md border border-gray-300 hover:border-golden hover:bg-golden transition-colors hidden lg:block">
                    <span>
                        @svg('eva-shopping-cart-outline', 'w-6 h-5 text-white')
                    </span>
                </button>
                <!-- Wishlist icon -->
                <button wire:click="addToWishlist({{ $product->id }})"
                    class="p-1.5 rounded-full hover:bg-red-500 transition-colors hidden lg:block">
                    <span title="Add to Wishlist">
                        @svg('eva-heart', 'w-6 h-5 text-white')
                    </span>
                </button>
            </div>
        </div>

        <!-- Price tag overlay - always visible -->
        <div class="absolute top-4 right-4 bg-black bg-opacity-80 text-white px-3 py-1 rounded-full">
            <span class="font-bold text-golden">{{ $product->price }}</span>
        </div>
    </div>

    <!-- Product Info -->
    <div class="px-5 py-4">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <!-- Larger, more prominent product name -->
                <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $product->name }}</h3>

                <!-- Secondary price display -->
                <div class="flex items-center mb-2">
                    <span class="text-md font-bold text-gray-600 mr-2">
                        @if ($product->status == 'available')
                            In Stock
                        @else
                            Out Of Stock
                        @endif
                    </span>

                </div>

                {{-- <!-- Rating if available -->
                @if (isset($product->rating))
                    <div class="flex items-center mt-1">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $product->rating)
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.799-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.799-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                            @endif
                        @endfor
                        <span class="ml-1 text-xs text-gray-500">
                            @if (isset($product->review_count))
                                ({{ $product->review_count }})
                            @endif
                        </span>
                    </div>
                @endif --}}
            </div>

            <div class="flex gap-2">
                <button wire:click="addToCart({{ $product->id }})" class="p-2 rounded-md border transition-colors">
                    <span>
                        @svg('eva-shopping-cart-outline', 'w-6 h-5 text-navy-blue fill-navy-blue')
                        {{-- @svg('eva-shopping-cart', 'w-6 h-5 text-navy-blue fill-navy-blue') --}}
                    </span>
                </button>

                <!-- Wishlist icon -->

                <button wire:click="addToWishlist({{ $product->id }})" id="wishlist"
                    class="p-1.5 rounded-full transition-colors group">

                    @svg('eva-heart-outline', 'w-7 h-5 text-red-500 hover:text-navy-blue')
                    {{-- @svg('eva-heart', 'w-6 h-5 text-red-500 hover:text-white') --}}

                </button>
            </div>

        </div>

        <!-- Category or product type badge -->
        @if (isset($product->category))
            <div class="mt-2 mb-3 flex ">
                <div class=" w-[70%]">
                    <span
                        class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-md ">{{ $product->category }}</span>
                </div>
                <div class="w-[30%]">
                    <x-bladewind.button wire:click="orderModal({{ $product->id }})" size="tiny" radius="small"
                        type="bg-navy-blue hover:bg-golden focus:ring-0" button_text_css="text-white" class="flex-1 ">
                        Buy Now
                    </x-bladewind.button>
                </div>
            </div>
        @endif
        <!-- Action Buttons -->

        <!-- Brief description if available -->
        @if (isset($product->short_description))
            <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $product->short_description }}</p>
        @endif
    </div>


</div>
