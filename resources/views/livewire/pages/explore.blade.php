<?php

use App\Models\User;
use App\Models\Contest;
use App\Models\Product;
use Livewire\Volt\Component;
use App\Models\ContestWinner;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, layout};

layout('layouts.app');

state(['latestDesigns' => fn() => Product::latest()->take(7)->get()]);
state(['pickedForYous' => fn() => auth()->user()->pickedForYou()]);
state(['whoRockedItBests' => fn() => Contest::where('type','=','who_rocked_it_best')->latest()->take(7)->get()]);
state(['trendingDesigns' => fn() => Product::daily()->take(7)->get()]);
state(['designersOfTheWeek' => fn() => ContestWinner::weekly()->take(7)->get()]);
state(['featuredDesigns' => fn() => Product::inRandomOrder()->take(7)->get()]);

?>

<div>
    <div class="flex gap-1 w-[100%]  h-screen pb-2">

        <div class="bg-white px-8 md:px-16 py-8  w-[100%]  pb-20 overflow-y-scroll mb-16 scrollbar-none">
            <h1 class="w-full text-3xl font-extrabold text-gray-500 md:hidden">Explore</h1>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Latest Designs</p>

                <h1 class="hidden text-3xl font-extrabold text-gray-400 md:block">Explore</h1>
                <a href="marketplace/latest-designs">
                    <p class="flex justify-between font-extrabold text-golden">See all
                        <svg class="w-[14px] h-[14px] my-2 ml-1" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path fill="#fbaa0d"
                                d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                        </svg>
                    </p>
                </a>
            </div>

            <div
                class="relative hidden w-full gap-1 py-2 bg-gray-100 md:grid md:px-2 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($latestDesigns as $latestDesign)
                    <x-explore-card wire:navigate :product="$latestDesign" />


                @empty
                    <p class="text-gray-400">No Latest Designs</p>
                @endforelse
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Picked For You</p>
                <a href="marketplace/picked-for-you">
                    <p class="flex justify-between font-extrabold text-golden">See all
                        <svg class="w-[14px] h-[14px] my-2 ml-1" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path fill="#fbaa0d"
                                d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                        </svg>
                    </p>
                </a>
            </div>
            <div
                class="relative flex justify-center w-full gap-3 py-3 md:hidden md:px-5 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($pickedForYous as $pickedForYou)
                    <x-explore-card wire:navigate :product="$pickedForYou" />


                @empty
                    <p class="text-gray-400">No Latest Designs</p>
                @endforelse

            </div>
            <div
                class="relative hidden w-full gap-3 py-3 bg-gray-100 md:grid md:px-5 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($pickedForYous as $pickedForYou)
                    <x-explore-card wire:navigate :product="$pickedForYou" />


                @empty
                    <p class="text-gray-400">No Latest Designs</p>
                @endforelse
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Who Rocked It Best</p>
                <a href="marketplace/who-rocked-it-best">
                    <p class="flex justify-between font-extrabold text-golden">See all
                        <svg class="w-[14px] h-[14px] my-2 ml-1" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path fill="#fbaa0d"
                                d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                        </svg>
                    </p>
                </a>
            </div>
            <div
                class="relative flex justify-center w-full gap-3 py-3 md:hidden md:px-5 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($whoRockedItBests as $whoRockedItBest)
                    <div class=" rounded-xl">
                        <img class="w-full h-full rounded-xl md:h-20 md:w-28 lg:h-32 lg:w-40 aspect-square"
                            src='{{ asset("uploads/contest/".$whoRockedItBest->photo) }}' alt="">
                    </div>


                @empty
                    <p class="text-gray-400">No Designs</p>
                @endforelse

            </div>
            <div
                class="relative hidden w-full gap-3 py-3 bg-gray-100 md:grid md:px-5 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                       @forelse ($whoRockedItBests as $whoRockedItBest)
                <div class=" rounded-xl">
                        <img class="w-full h-full rounded-xl md:h-20 md:w-28 lg:h-32 lg:w-40 aspect-square"
                            src='{{ asset("uploads/contest/".$whoRockedItBest->photo) }}' alt="">
                    </div>
                @empty
                    <p class="text-gray-400">No Designs</p>
                @endforelse
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[23px]">Trending Designs</p>
                <a href="marketplace/trending-designs">
                    <p class="flex justify-between font-extrabold text-golden">See all
                        <svg class="w-[14px] h-[14px] my-2 ml-1" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path fill="#fbaa0d"
                                d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                        </svg>
                    </p>
                </a>
            </div>
            <div
                class="relative flex justify-center w-full gap-3 py-3 md:hidden md:px-5 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($trendingDesigns as $trendingDesign)
                    <x-explore-card wire:navigate :product="$trendingDesign" />


                @empty
                    <p class="text-gray-400">No Trending Designs</p>
                @endforelse

            </div>
            <div
                class="relative hidden w-full gap-3 py-3 bg-gray-100 md:grid md:px-5 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($trendingDesigns as $trendingDesign)
                    <x-explore-card wire:navigate :product="$trendingDesign" />


                @empty
                    <p class="text-gray-400">No Trending Designs</p>
                @endforelse
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Designers Of The Week</p>
                <a href="marketplace/designers-of-the-week">
                    <p class="flex justify-between font-extrabold text-golden">See all
                        <svg class="w-[14px] h-[14px] my-2 ml-1" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path fill="#fbaa0d"
                                d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                        </svg>
                    </p>
                </a>
            </div>
            <div
                class="relative flex justify-center w-full gap-3 py-3 md:hidden md:px-5 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($designersOfTheWeek as $designWeekly)
                    <x-explore-card wire:navigate :product="$designWeekly" />


                @empty
                    <p class="text-gray-400">No Weekly Designs</p>
                @endforelse

            </div>
            <div
                class="relative hidden w-full gap-3 py-3 bg-gray-100 md:grid md:px-5 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($designersOfTheWeek as $designWeekly)
                    <x-explore-card wire:navigate :product="$designWeekly" />


                @empty
                    <p class="text-gray-400">No Weekly Designs</p>
                @endforelse
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Featured Designs</p>
                <a href="marketplace/featured-designs">
                    <p class="flex justify-between font-extrabold text-golden">See all
                        <svg class="w-[14px] h-[14px] my-2 ml-1" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path fill="#fbaa0d"
                                d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                        </svg>
                    </p>
                </a>
            </div>
            <div
                class="relative flex justify-center w-full gap-3 py-3 md:hidden md:px-5 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($featuredDesigns as $design)
                    <x-explore-card wire:navigate :product="$design" />


                @empty
                    <p class="text-gray-400">No Featured Designs</p>
                @endforelse

            </div>
            <div
                class="relative hidden w-full gap-3 py-3 bg-gray-100 md:grid md:px-5 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                @forelse ($featuredDesigns as $design)
                    <x-explore-card wire:navigate :product="$design" />

                @empty
                    <p class="text-gray-400">No Featured Designs</p>
                @endforelse
            </div>
        </div>

    </div>
