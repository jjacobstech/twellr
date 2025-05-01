<?php



use App\Models\AdminSetting;
use App\Models\Notification;
use Livewire\Volt\Component;
use function Livewire\Volt\{layout, mount};





 ?>

<div class="h-full">

    <!-- Product -->
    <div class="flex w-[100%] space-x-1 h-full">
            <div x-transition.opacity
                class="fixed inset-0 z-50 w-screen h-full px-5 py-16 sm:py-12 md:py-12 md:px-20 lg:py-12 bg-black/40 backdrop-blur-sm pb-26">
                <div class="grid h-full lg:flex justify-evenly rounded-xl md:flex-row">
                    <div class=" object-fit-contain lg:w-[75%] carousel rounded-t-xl md:rounded-none lg:rounded-l-xl">
                        <div class="relative w-full carousel-item" id="front-view">
                            <img src="@if ($product) {{ asset('uploads/products/design-stack/' . $product->front_view) }} @endif"
                                alt="shopping image" class="object-cover w-full h-full ">

                            <div
                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                <a href="#side-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                <a href="#back-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                            </div>
                        </div>
                        <div class="relative w-full carousel-item" id="back-view">
                            <img src="@if ($product) {{ asset('uploads/products/design-stack/' . $product->back_view) }} @endif"
                                alt="shopping image" class="object-cover w-full h-full ">
                            <div
                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                <a href="#front-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                <a href="#side-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                            </div>
                        </div>
                        <div class="relative w-full carousel-item" id="side-view">
                            <img src="@if ($product) {{ asset('uploads/products/design-stack/' . $product->side_view) }} @endif"
                                alt="shopping image" class="object-cover w-full h-full ">
                            <div
                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                <a href="#back-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                <a href="#front-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                            </div>
                        </div>
                    </div>

                    <div
                        class="lg:w-[25%] px-6 md:px-10 lg:px-6 bg-white lg:rounded-r-xl space-y-3 overflow-y-scroll py-5">
                        <!-- design info -->
                        <div class="flex flex-wrap mt-5 ">
                            <h1 class="flex-auto text-xl font-semibold text-black">
                                @if ($product)
                                    {{ $product->name }}
                                @endif
                            </h1>
                            <div class="text-xl font-semibold text-black">
                                @if ($product)
                                    {{ $product->price }}
                                @endif
                            </div>
                            <div class="flex-none w-full mt-2 font-extrabold text-black text-md ">
                                @if ($product)
                                    {{ $product->category }}
                                @endif
                            </div>
                        </div>

                        <!-- Order Button -->

                                <x-mary-button label="Order"
                                    class="bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                    wire:click="{{ route('register') }}" spinner />

                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

