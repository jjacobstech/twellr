<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,

}">
    <div class="flex gap-1 w-[100%]  h-screen">
        <div class="bg-white px-8 md:px-16 py-8  w-[100%]  pb-20 overflow-y-scroll ">
            <h1 class="text-3xl w-full  font-extrabold text-gray-500 md:hidden">Explore</h1>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Latest Designs</p>

                <h1 class="text-3xl font-extrabold text-gray-400 hidden md:block">Explore</h1>
                <a href="">
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
                class="relative md:hidden flex justify-center w-full gap-3 md:px-5 py-3 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />

            </div>
            <div
                class="relative hidden  md:grid w-full gap-3 md:px-5 py-3 bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Picked For You</p>
                <a href="">
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
                class="relative md:hidden flex justify-center w-full gap-3 md:px-5 py-3 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />

            </div>
            <div
                class="relative hidden  md:grid w-full gap-3 md:px-5 py-3 bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Who Rocked It Best</p>
                <a href="">
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
                class="relative md:hidden flex justify-center w-full gap-3 md:px-5 py-3 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />

            </div>
            <div
                class="relative hidden  md:grid w-full gap-3 md:px-5 py-3 bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[23px]">Trending Designs</p>
                <a href="">
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
                class="relative md:hidden flex justify-center w-full gap-3 md:px-5 py-3 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />

            </div>
            <div
                class="relative hidden  md:grid w-full gap-3 md:px-5 py-3 bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Designers Of The Week</p>
                <a href="">
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
                class="relative md:hidden flex justify-center w-full gap-3 md:px-5 py-3 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />

            </div>
            <div
                class="relative hidden  md:grid w-full gap-3 md:px-5 py-3 bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
            </div>
            <div class="flex justify-between w-full my-3 text-lg">
                <p class="font-extrabold text-gray-400 text-[21px]">Featured Designs</p>
                <a href="">
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
                class="relative md:hidden flex justify-center w-full gap-3 md:px-5 py-3 md:bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />

            </div>
            <div
                class="relative hidden  md:grid w-full gap-3 md:px-5 py-3 bg-gray-100 grid-col-4 md:grid-cols-7 sm:grid-cols-2 rounded-2xl">
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
                <x-explore-card wire:navigate />
            </div>

        </div>

    </div>
