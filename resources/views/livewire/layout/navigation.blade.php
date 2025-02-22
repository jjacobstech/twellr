<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */

    public function __invoke()
    {
        return cache()->putMany(['user' => auth()->user(), 'url' => url()->current()]);
    }

    public function logout(Logout $logout): void
    {
        auth()->logout();

        $this->redirect('/', navigate: true);
    }
};
?>


<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="px-2 mx-auto max-w-7xl sm:px-6 lg:px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a class="flex" href="{{ route('admin.dashboard') }}" wire:navigate>
                        <x-application-logo class="block w-auto text-gray-800 fill-current h-9 dark:text-gray-200" />
                        <img class="h-5 px-3 my-1" src="{{ asset('assets/twellr-text.png') }}" alt="">
                    </a>
                </div>

            </div>

            <div class="w-full sm:-my-px sm:ms-10 sm:flex">
                {{-- Search Bar --}}
                <form class="w-[75%] px-10 ">
                    <div class="flex w-full">
                        <div class="relative flex w-full my-3">
                            <input type="search" id="search-dropdown"
                                class="font-bold block p-2.5 w-full  z-20 text-sm text-gray-900 bg-gray-200 rounded-l-lg border-0 active:border-0 hover:border hover:border-gray-400 focus:border-0 focus:ring-0 border-navy-blue "
                                placeholder="Search by: Creator, Design, Location, Ratings"
                                alt="Search by: Creator, Design, Location, Ratings"
                                title='Search by: Creator, Design, Location, Ratings' required />
                            <button type="submit"
                                class=" top-0 end-0 p-2.5 text-sm font-medium h-full text-white bg-gray-200 border-0 rounded-e-lg active:bg-white active:text-navy-blue border-navy-blue hover:bg-navy-blue focus:ring-0 focus:outline-none ">
                                <svg class="w-4 h-4 text-[#fbaa0d]" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                                <span class="sr-only">Search</span>
                            </button>
                        </div>
                    </div>
                </form>

                @if (Auth::user()->role != 'admin')
                    <div class="justify-center flex w-[10%] mt-5 text-[#b7c1ab]">
                        <span class="w-[20%] mx-1 py-1">
                            <svg viewBox="0 -3.5 29 29" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" fill="#000000" stroke="#000000">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <title>bullet-list</title>
                                    <desc>Created with Sketch Beta.</desc>
                                    <defs> </defs>
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
                                        sketch:type="MSPage">
                                        <g id="Icon-Set-Filled" sketch:type="MSLayerGroup"
                                            transform="translate(-571.000000, -210.000000)" fill="#888a85">
                                            <path
                                                d="M598,227 L582,227 C580.896,227 580,227.896 580,229 C580,230.104 580.896,231 582,231 L598,231 C599.104,231 600,230.104 600,229 C600,227.896 599.104,227 598,227 L598,227 Z M598,219 L582,219 C580.896,219 580,219.896 580,221 C580,222.104 580.896,223 582,223 L598,223 C599.104,223 600,222.104 600,221 C600,219.896 599.104,219 598,219 L598,219 Z M582,215 L598,215 C599.104,215 600,214.104 600,213 C600,211.896 599.104,211 598,211 L582,211 C580.896,211 580,211.896 580,213 C580,214.104 580.896,215 582,215 L582,215 Z M574,226 C572.343,226 571,227.343 571,229 C571,230.657 572.343,232 574,232 C575.657,232 577,230.657 577,229 C577,227.343 575.657,226 574,226 L574,226 Z M574,218 C572.343,218 571,219.343 571,221 C571,222.657 572.343,224 574,224 C575.657,224 577,222.657 577,221 C577,219.343 575.657,218 574,218 L574,218 Z M574,210 C572.343,210 571,211.343 571,213 C571,214.657 572.343,216 574,216 C575.657,216 577,214.657 577,213 C577,211.343 575.657,210 574,210 L574,210 Z"
                                                id="bullet-list" sketch:type="MSShapeGroup"> </path>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </span>
                        <span class="capitalize font-bold text-lg text-[#909090]">More</span>
                    </div>
                    <div class="flex justify-center ">
                        <span class="w-[20%] py-3">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="40" height="40" viewBox="0 0 132 139">
                                <image
                                    xlink:href="data:image/png;base64,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"
                                    x="0" y="0" width="132" height="139" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex justify-center ml-8">
                        <span class="py-5 mx-4">
                            <svg height="25px" width="25px" version="1.1" id="Layer_1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                viewBox="-51.2 -51.2 614.40 614.40" xml:space="preserve" fill="#000000"
                                transform="rotate(0)">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path style="fill:#fbaa0d;"
                                        d="M256,100.174c-27.619,0-50.087-22.468-50.087-50.087S228.381,0,256,0s50.087,22.468,50.087,50.087 S283.619,100.174,256,100.174z M256,33.391c-9.196,0-16.696,7.5-16.696,16.696s7.5,16.696,16.696,16.696 c9.196,0,16.696-7.5,16.696-16.696S265.196,33.391,256,33.391z">
                                    </path>
                                    <path style="fill:#fbaa0d;"
                                        d="M256.006,0v33.394c9.194,0.003,16.69,7.5,16.69,16.693s-7.496,16.69-16.69,16.693v33.394 c27.618-0.004,50.081-22.469,50.081-50.087S283.624,0.004,256.006,0z">
                                    </path>
                                    <path style="fill:#fbaa0d;"
                                        d="M256,512c-46.043,0-83.478-37.435-83.478-83.478c0-9.228,7.467-16.696,16.696-16.696h133.565 c9.228,0,16.696,7.467,16.696,16.696C339.478,474.565,302.043,512,256,512z">
                                    </path>
                                    <path style="fill:#fbaa0d;"
                                        d="M322.783,411.826h-66.777V512c46.042-0.004,83.473-37.437,83.473-83.478 C339.478,419.293,332.011,411.826,322.783,411.826z">
                                    </path>
                                    <path style="fill:#fbaa0d;"
                                        d="M439.652,348.113v-97.678C439.642,149,357.435,66.793,256,66.783 C154.565,66.793,72.358,149,72.348,250.435v97.678c-19.41,6.901-33.384,25.233-33.391,47.017 c0.01,27.668,22.419,50.075,50.087,50.085h333.913c27.668-0.01,50.077-22.417,50.087-50.085 C473.036,373.346,459.063,355.014,439.652,348.113z">
                                    </path>
                                    <path style="fill:#fbaa0d;"
                                        d="M439.652,348.113v-97.678C439.642,149,357.435,66.793,256,66.783v378.432h166.957 c27.668-0.01,50.077-22.417,50.087-50.085C473.036,373.346,459.063,355.014,439.652,348.113z">
                                    </path>
                                    <path style="fill:#FFF3DB;"
                                        d="M155.826,267.13c-9.228,0-16.696-7.467-16.696-16.696c0-47.022,28.011-89.283,71.381-107.641 c8.446-3.587,18.294,0.326,21.88,8.836c3.62,8.51-0.358,18.294-8.836,21.88c-31.012,13.142-51.033,43.337-51.033,76.925 C172.522,259.663,165.054,267.13,155.826,267.13z">
                                    </path>
                                </g>
                            </svg></span>
                    </div>
                @endif
                @if (Auth::user()->role == 'admin')
                    <div class="py-5 px-2 w-[25%] text-right text-lg font-bold text-black">
                        {{ Auth::user()->name }}
                        </>
                    </div>
                @endif

            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:mr-7">

                <x-avatar />
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-1 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-on:profile-updated.window="name = $event.detail.name">
                            </div>

                            <div class="">
                                <svg class="w-5 h-5 font-bold fill-current " xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>




            <!-- Hamburger -->
            <div class="flex items-center -me-2 sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="flex">
                <x-avatar />
                <div class="px-4">
                    <div class="text-base font-medium text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                        x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
