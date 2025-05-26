<div
    class="w-full max-w-sm transition-shadow duration-300 shadow-md md:overflow-hidden rounded-xl hover:shadow-lg aspect-square">
    <!-- Product Image Container with fixed aspect ratio -->
    <div class="relative group aspect-square">
        <img class="object-cover w-full h-full aspect-square rounded-t-xl lg:rounded-t-none"
            src="{{ asset('uploads/products/design-stack/' . $design->front_view) }}" alt="{{ $design->name }}">

        <!-- Quick view overlay - appears on hover (desktop only) -->
        <div
            class="absolute inset-0 items-center justify-center hidden transition-opacity duration-300 bg-black opacity-0 lg:flex bg-opacity-40 group-hover:opacity-100 rounded-t-xl lg:rounded-t-none">
            <!-- Action Buttons for Desktop -->

                <div class="flex px-5 pb-4 space-x-2">
                    <x-bladewind.button wire:click="submit({{ $design->id }})" size="small" radius="medium"
                        type="bg-navy-blue hover:bg-golden focus:ring-0" button_text_css="text-white" class="flex-1">
                            {{ $design->inDesignFest() ? "Submitted" : "Submit" }}
                    </x-bladewind.button>

                </div>

                <div class="flex px-5 pb-4 space-x-2">
                    <x-bladewind.button wire:click="submit({{ $design->id }})" size="small" radius="medium"
                        type="bg-navy-blue hover:bg-golden focus:ring-0" button_text_css="text-white" class="flex-1">
                            Comments
                    </x-bladewind.button>

                </div>

        </div>

        <!-- Price tag overlay - always visible -->
        <div class="absolute px-2 py-1 text-white bg-black rounded-full sm:px-3 top-2 right-2 bg-opacity-80">
            <span class="text-xs font-bold sm:text-sm md:text-base text-golden">
                {{ App\Models\AdminSetting::first()->value('currency_symbol') . $design->price }}
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
                    <h3 class="w-20 mb-1 text-lg font-bold text-gray-800 truncate sm:text-xl">{{ $design->name }}</h3>


                </div>


            </div>

            <!-- Submit button -->
            <div class="flex items-center justify-between gap-1 mt-2 mb-2">


                    <div class="flex-shrink-0">
                        <x-bladewind.button wire:click="submit({{ $design->id }})" size="tiny" radius="small"
                            type="bg-navy-blue hover:bg-golden focus:ring-0 px-1" button_text_css="text-white"
                            class="md:w-full">
                          {{ $design->inDesignFest() ? "Submitted" : "Submit" }}
                        </x-bladewind.button>
                    </div>

            </div>
            <!-- Comments button -->
            <div class="flex items-center justify-between gap-1 mt-2 mb-2">


                    <div class="flex-shrink-0">
                        <x-bladewind.button wire:click="submit({{ $design->id }})" size="tiny" radius="small"
                            type="bg-navy-blue hover:bg-golden focus:ring-0 px-1" button_text_css="text-white"
                            class="md:w-full">
                        Comments
                        </x-bladewind.button>
                    </div>

            </div>
        </div>

        <!-- Desktop View - Minimalist approach -->
        <div class="hidden lg:block">
            <h3 class="text-lg font-bold text-gray-800 truncate">{{ $design->name }}</h3>
            <div class="flex items-center justify-between mt-2">
                <span class="inline-block px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-md">
                    {{ $design->category->name }}
                </span>
                <span class="text-xs font-bold text-gray-600">
                    @if ($design->status == 'available')
                        In Stock
                    @else
                        Out Of Stock
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
