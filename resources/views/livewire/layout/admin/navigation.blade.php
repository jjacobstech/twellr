<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Notification;
use Livewire\Volt\Component;
use App\Livewire\Actions\Logout;

new class extends Component {
    public bool $notification = false;
    public $notifications;
    public $redirection;
    public bool $isCreative;
    public bool $isAdmin;
    public bool $drawer = false;
    public $result;
    public $userActions;
    public $keyword = '';

    public function mount()
    {
        $this->redirection = url()->current();
        $notifications = Notification::where('user_id', '=', Auth::id())->where('read_at', '=', null)->get();
        $this->notification = $notifications->isEmpty() ? false : true;

        $this->notifications = $notifications;
        $this->isCreative = Auth::user()->isCreative();
        $this->isAdmin = Auth::user()->isAdmin();
    }
    public function closeDrawer()
    {
        $this->drawer = false;
    }

    public function search($keyword = '')
    {
        // Example of security concern
        // Guests can not search
        if ($keyword != '') {
            if (!Auth::user()) {
                $this->result = $this->users($keyword);
                //    $this->userActions = $this->actions;
            }

            if (Auth::user()) {
                $this->result = $this->users($keyword);
            }
        } else {
            $this->result = '';
        }
    }

    // Database search
    public function users(string $search = '')
    {
        return User::query()
            ->where('firstname', 'like', "%$search%")
            ->orWhere('lastname', 'like', "%$search%")
            ->orWhere('instagram', 'like', "%$search%")
            ->take(5)
            ->get()
            ->map(function (User $user) {
                return (object) [
                    'avatar' => $user->avatar ? asset('uploads/avatar/' . $user->avatar) : asset('assets/icons-user.png'),
                    'name' => "$user->firstname $user->lastname",
                    'email' => $user->email,
                    'link' => '/r/' . $user->referral_link,
                ];
            });
    }

    // Static search, but it could come from a database
    public function actions()
    {
        return (object) [
            'register' => [
                'name' => 'Register',
                'description' => 'Create A Twellr Account',
                'icon' => "<x-mary-icon name='o-user' class='p-2 rounded-full w-11 h-11 bg-yellow-50' />",
                'link' => route('register'),
            ],
            'explore' => [
                'name' => 'Explore',
                'description' => 'Discover New Creatives',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5m0-10v10"/></svg>',
                'link' => route('explore'),
            ],
            'blog' => [
                'name' => 'Blog',
                'description' => 'Read The Latest News',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3V3z"/><path d="M3 9h18M3 15h18"/></svg>',
                'link' => route('blog'),
            ],
            'marketplace' => [
                'name' => 'Marketplace',
                'description' => 'Buy & Sell Designs',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5m0-10v10"/></svg>',
                'link' => route('market.place'),
            ],
            'support' => [
                'name' => 'Support',
                'description' => 'Get Help & Support',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5m0-10v10"/></svg>',
                'link' => route('support'),
            ],
        ];
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('id', '=', $id)->first();
        $notification->fill(['read_at' => now()]);
        $notification->save();
        $this->redirectIntended($this->redirection, true);
    }

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


<nav x-data="{ open: false, more: false, notification: false, term: '', admin: !$wire.isAdmin }" class="bg-white border-gray-100">

    <!-- Primary Navigation Menu -->
    <div class="px-5 mx-auto md:px-2 max-w-7xl sm:px-6 lg:px-4">

        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a class="flex" href="{{ route('admin.dashboard') }}" wire:navigate>
                        <x-application-logo class="block w-auto fill-current h-9" />
                        <img class="hidden md:h-5 md:px-3 md:my-1 md:block" src="{{ asset('assets/twellr-text.png') }}"
                            alt="">
                    </a>
                </div>

            </div>
            <div class="w-full sm:-my-px md:ms-10 sm:flex">
                {{-- Search Bar --}}
                <form class="w-full px-3 sm:px-5 md:px-9">
                    <div class="flex justify-between w-full">
                        <div class="relative flex w-full py-2 md:py-3">
                            <input type="text" id="search-dropdown" x-model="term" x-on:keydown="$wire.search(term)"
                                x-on:keyup="$wire.search(term)"
                                class="font-bold block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-200 rounded-lg border-0 active:border-0 hover:border hover:border-gray-400 focus:border-0 focus:ring-0 border-navy-blue "
                                placeholder="Search by: Creator, Design, Location, Ratings"
                                alt="Search by: Creator, Design, Location, Ratings"
                                title='Search by: Creator, Design, Location, Ratings' />
                            <span
                                class="top-0 end-0 p-2.5 text-sm font-medium absolute z-50 mt-3.5 text-white bg-gray-200 border-0 rounded-l-lg mr-1  focus:ring-0 focus:outline-none ">
                                <svg class="w-4 h-4 text-[#fbaa0d]" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                                <span class="sr-only">Search</span>
                            </span>

                            @if ($result != '')
                                <div class="absolute top-full left-0 w-full z-[9999]">
                                    <div x-show="term != ''" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        class="bg-white rounded-md shadow-lg mt-1 max-h-96 overflow-y-auto">
                                        <div class="w-full p-2 sm:p-3">
                                            <div class="flex flex-col gap-1 sm:gap-2">

                                                @if ($result->isEmpty())

                                                                <p class="text-md font-bold text-black">Opps! Nothing
                                                                    Found</p>

                                                @endif

                                                @foreach ($result as $item)
                                                    <a href="{{ $item->link }}"
                                                        class="flex items-center gap-2 p-1 sm:p-2 border-b border-gray-200 hover:bg-gray-100 transition-colors">
                                                        <img src="{{ $item->avatar }}" alt="{{ $item->name }}"
                                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                                        <div class="min-w-0 flex-1">
                                                            <h4
                                                                class="text-xs sm:text-sm font-bold text-gray-800 truncate">
                                                                {{ $item->name }}</h4>
                                                            <p class="text-xs text-gray-500 truncate">
                                                                {{ $item->email }}</p>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endif
                        </div>
                    </div>
                </form>
                {{-- Search Bar End --}}

                {{-- More --}}
                <div class="justify-center w-100 sm:flex   md:w-[10%] mt-3 text-[#b7c1ab] hidden hover:cursor-pointer">
                    <span class="w-5 h-5 pt-2 mx-1 ">
                        <svg class="w-5 h-5" viewBox="0 -3.5 29 29" version="1.1" xmlns="http://www.w3.org/2000/svg"
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


                    <span class="capitalize font-bold text-lg text-[#909090]  mt-1">More</span>
                    <x-dropdown contentClass="mt-5" align="right" width="48" class="shadow-2xl shadow-navy-blue">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center text-sm font-medium leading-4 text-gray-500 transition duration-150 mt-1.5 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                                <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-on:profile-updated.window="name = $event.detail.name">
                                </div>

                                <div class="">
                                    <svg class="w-5 h-5 font-bold text-gray-400 fill-current"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                        <path
                                            d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link x-show="$wire.isCreative" class="hidden text-bold sm:block"
                                :href="route('creative.upload')">
                                <span>
                                    <svg class="inline-block w-5 h-5 fill-navy-blue " version="1.1" id="Capa_1"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="0 0 384.97 384.97" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <g id="Upload">
                                                    <path
                                                        d="M372.939,264.641c-6.641,0-12.03,5.39-12.03,12.03v84.212H24.061v-84.212c0-6.641-5.39-12.03-12.03-12.03 S0,270.031,0,276.671v96.242c0,6.641,5.39,12.03,12.03,12.03h360.909c6.641,0,12.03-5.39,12.03-12.03v-96.242 C384.97,270.019,379.58,264.641,372.939,264.641z">
                                                    </path>
                                                    <path
                                                        d="M117.067,103.507l63.46-62.558v235.71c0,6.641,5.438,12.03,12.151,12.03c6.713,0,12.151-5.39,12.151-12.03V40.95 l63.46,62.558c4.74,4.704,12.439,4.704,17.179,0c4.74-4.704,4.752-12.319,0-17.011l-84.2-82.997 c-4.692-4.656-12.584-4.608-17.191,0L99.888,86.496c-4.752,4.704-4.74,12.319,0,17.011 C104.628,108.211,112.327,108.211,117.067,103.507z">
                                                    </path>
                                                </g>
                                                <g> </g>
                                                <g> </g>
                                                <g> </g>
                                                <g> </g>
                                                <g> </g>
                                                <g> </g>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                                {{ __('Upload') }}
                            </x-dropdown-link>

                            <x-dropdown-link class='hidden sm:block' :href="route('explore')" :active="request()->routeIs('explore')" wire:navigate>

                                <span>
                                    <svg class="inline-block w-6 h-6 fill-current text-navy-blue"
                                        xmlns="http://www.w3.org/2000/svg" id="explore">
                                        <path fill="none" d="M0 0h24v24H0V0z"></path>
                                        <path
                                            d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z">
                                        </path>
                                    </svg>
                                </span>
                                {{ __('Explore') }}
                            </x-dropdown-link>


                            <x-dropdown-link class="hidden text-bold sm:block" :href="route('blog')" wire:navigate>
                                <span>
                                    <svg class="inline-block w-5 h-5 fill-current" id="Layer_1" data-name="Layer 1"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <defs>
                                            <style>
                                                .cls-1 {
                                                    fill: #141f38;
                                                }
                                            </style>
                                        </defs>
                                        <title>browser-3-glyph</title>
                                        <path class="cls-1"
                                            d="M448,0H64A64,64,0,0,0,0,64v51.2H512V64A64,64,0,0,0,448,0ZM70.4,76.8A19.2,19.2,0,1,1,89.6,57.6,19.2,19.2,0,0,1,70.4,76.8Zm51.2,0a19.2,19.2,0,1,1,19.2-19.2A19.2,19.2,0,0,1,121.6,76.8Zm51.2,0A19.2,19.2,0,1,1,192,57.6,19.2,19.2,0,0,1,172.8,76.8ZM0,448a64,64,0,0,0,64,64H448a64,64,0,0,0,64-64V140.8H0ZM294.4,192H435.2a12.8,12.8,0,1,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,51.2H435.2a12.8,12.8,0,1,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,51.2H384a12.8,12.8,0,0,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,76.8H435.2a12.8,12.8,0,1,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,51.2H384a12.8,12.8,0,0,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6ZM64,211.2a32,32,0,0,1,32-32H211.2a32,32,0,0,1,32,32V416a32,32,0,0,1-32,32H96a32,32,0,0,1-32-32Z" />
                                    </svg>
                                </span>
                                {{ __('Blog') }}
                            </x-dropdown-link>
                            <x-dropdown-link class="hidden text-bold sm:block" :href="route('market.place')" wire:navigate>
                                <span>
                                    <svg class="inline-block w-5 h-5 fill-current" fill="#000000" height="200px"
                                        width="200px" version="1.1" id="Capa_1"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="0 0 511 511" xml:space="preserve">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path
                                                    d="M503.5,440H479V207.433c13.842-3.487,24-16.502,24-31.933v-104c0-8.547-6.953-15.5-15.5-15.5h-464 C14.953,56,8,62.953,8,71.5v104c0,15.432,10.158,28.446,24,31.933V440H7.5c-4.142,0-7.5,3.358-7.5,7.5s3.358,7.5,7.5,7.5h496 c4.142,0,7.5-3.358,7.5-7.5S507.642,440,503.5,440z M488,71.5v104c0,9.383-6.999,17.384-15.602,17.834 c-4.595,0.235-8.939-1.36-12.254-4.505c-3.317-3.148-5.145-7.4-5.145-11.971V71h32.5C487.776,71,488,71.224,488,71.5z M71,71h33 v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M119,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5 s-16.5-7.402-16.5-16.5V71z M167,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M215,71h33v105.858 c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M263,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5 V71z M311,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M359,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5 s-16.5-7.402-16.5-16.5V71z M407,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M23,175.5v-104 c0-0.276,0.224-0.5,0.5-0.5H56v105.858c0,4.571-1.827,8.823-5.145,11.971c-3.314,3.146-7.663,4.743-12.254,4.505 C29.999,192.884,23,184.883,23,175.5z M47,207.462c5.266-1.279,10.128-3.907,14.181-7.753c0.822-0.78,1.599-1.603,2.326-2.462 c5.782,6.793,14.393,11.11,23.993,11.11c9.604,0,18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119 c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119 c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119 c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119c9.6,0,18.21-4.317,23.993-11.11 c0.728,0.859,1.504,1.682,2.326,2.462c4.054,3.847,8.914,6.482,14.181,7.761V440h-33V263.5c0-8.547-6.953-15.5-15.5-15.5h-96 c-8.547,0-15.5,6.953-15.5,15.5V440H47V207.462z M416,440h-97V263.5c0-0.276,0.224-0.5,0.5-0.5h96c0.276,0,0.5,0.224,0.5,0.5V440z">
                                                </path>
                                                <path
                                                    d="M343.5,336c-4.142,0-7.5,3.358-7.5,7.5v16c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-16 C351,339.358,347.642,336,343.5,336z">
                                                </path>
                                                <path
                                                    d="M262.5,248h-174c-4.687,0-8.5,3.813-8.5,8.5v142c0,4.687,3.813,8.5,8.5,8.5h174c4.687,0,8.5-3.813,8.5-8.5v-142 C271,251.813,267.187,248,262.5,248z M256,392H95V263h161V392z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                                {{ __('Marketplace') }}
                            </x-dropdown-link>

                        </x-slot>
                    </x-dropdown>
                </div>
                {{-- More End --}}

                {{-- Support --}}
                <div x-show="admin" class="justify-center hidden pl-3 md:flex">
                    <a class="my-2 " href="{{ route('support') }}">
                        <span class="py-3 w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="40" height="40" viewBox="0 0 132 139">
                                <image
                                    xlink:href="data:image/png;base64,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"
                                    x="0" y="0" width="132" height="139" />
                            </svg>
                        </span>
                    </a>
                </div>
                {{-- Support End --}}

                {{-- Notification --}}
                <div class="justify-center hidden mx-5 md:flex z-999">
                    <span class="z-20 py-5">
                        <x-dropdown width='w-[500px]' contentClasses="w-full rounded-md">
                            <x-slot name="trigger">
                                <x-bladewind.bell show_dot="{{ $notification }}" color="red"
                                    animate_dot="true" />
                            </x-slot>
                            <x-slot name="content">
                                <x-bladewind.dropmenu-item class="p-0 bg-white rounded-lg" hover="false"
                                    padded="false" transparent="false">
                                    <x-bladewind.list-view class="w-full py-2 bg-white" compact="true">
                                        @if ($notification)
                                            @foreach ($notifications as $notification)
                                                <x-bladewind.list-item
                                                    class="flex justify-between w-full bg-white hover:bg-gray-100">
                                                    <div class="pt-1 mx-1">
                                                        <div class="text-sm">
                                                            <span class="font-medium text-slate-900">
                                                                {{ $notification->message }}

                                                            </span>

                                                            <div class="text-xs">
                                                                {{ Carbon::parse($notification->created_at)->format('D-M-Y') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <x-bladewind.button
                                                        wire:click="markAsRead('{{ $notification->id }}')"
                                                        class="ml-20 hover:bg-navy-blue" type="bg-golden"
                                                        button_text_css="text-white" size="small">
                                                        <span>
                                                            Mark As Read
                                                        </span>
                                                    </x-bladewind.button>
                                                </x-bladewind.list-item>
                                            @endforeach
                                        @else
                                            <x-bladewind.list-item
                                                class="flex justify-between w-full bg-white hover:bg-gray-100">
                                                <div class="pt-1 mx-1">
                                                    <div class="text-sm font-extrabold">
                                                        <div class="grid justify-evenly">
                                                            <h3 class="text-lg font-extrabold text-gray-700">No
                                                                Notifications
                                                            </h3>
                                                            <p class="mt-1 text-sm text-gray-500">You're all
                                                                caught up!</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </x-bladewind.list-item>
                                        @endif
                                    </x-bladewind.list-view>
                                </x-bladewind.dropmenu-item>
                            </x-slot>
                        </x-dropdown>
                    </span>
                </div>
                {{-- Notification End --}}
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-5 md:ml-0 md:mr-7">

                <x-avatar />
                <x-dropdown align="right" width="48" class="shadow-2xl shadow-navy-blue ">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-1 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md dark:text-gray-400 hover:text-gray-700 focus:outline-none">
                            <div x-data="{{ json_encode(['name' => auth()->user()->firstname . ' ' . auth()->user()->lastname]) }}" x-on:profile-updated.window="name = $event.detail.name">
                            </div>

                            <div class="">
                                <svg class="w-5 h-5 font-bold text-gray-400 fill-current"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path
                                        d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Tablets - sm screen  --}}
                        <div class="hidden sm:block md:hidden">
                            <x-dropdown-link class=' ' :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                                <span>
                                    <svg class="inline-block w-6 h-6 fill-current" fill="currentColor"
                                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                        </path>
                                    </svg>
                                </span>
                                {{ __('Dashboard') }}
                            </x-dropdown-link>


                            <x-dropdown-link class="text-bold" :href="route('admin.settings')" wire:navigate>
                                <span>
                                    <svg class="inline-block w-5 h-5 fill-current text-navy-blue"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="settings">
                                        <path fill="none" d="M0 0h24v24H0V0z"></path>
                                        <path
                                            d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z">
                                        </path>
                                    </svg>
                                </span>
                                {{ __('Settings') }}
                            </x-dropdown-link>

                            <!-- Logout Button -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    <span>
                                        <svg class='inline-block w-5 h-5 fill-current text-navy-blue'
                                            viewBox="0 0 24 24" width='10' height='10' fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M17.2929 14.2929C16.9024 14.6834 16.9024 15.3166 17.2929 15.7071C17.6834 16.0976 18.3166 16.0976 18.7071 15.7071L21.6201 12.7941C21.6351 12.7791 21.6497 12.7637 21.6637 12.748C21.87 12.5648 22 12.2976 22 12C22 11.7024 21.87 11.4352 21.6637 11.252C21.6497 11.2363 21.6351 11.2209 21.6201 11.2059L18.7071 8.29289C18.3166 7.90237 17.6834 7.90237 17.2929 8.29289C16.9024 8.68342 16.9024 9.31658 17.2929 9.70711L18.5858 11H13C12.4477 11 12 11.4477 12 12C12 12.5523 12.4477 13 13 13H18.5858L17.2929 14.2929Z"
                                                    fill="navy-blue"></path>
                                                <path
                                                    d="M5 2C3.34315 2 2 3.34315 2 5V19C2 20.6569 3.34315 22 5 22H14.5C15.8807 22 17 20.8807 17 19.5V16.7326C16.8519 16.647 16.7125 16.5409 16.5858 16.4142C15.9314 15.7598 15.8253 14.7649 16.2674 14H13C11.8954 14 11 13.1046 11 12C11 10.8954 11.8954 10 13 10H16.2674C15.8253 9.23514 15.9314 8.24015 16.5858 7.58579C16.7125 7.4591 16.8519 7.35296 17 7.26738V4.5C17 3.11929 15.8807 2 14.5 2H5Z"
                                                    fill="navy-blue"></path>
                                            </g>
                                        </svg>
                                    </span>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </div>
                        {{-- Desktop --}}
                        <div class="hidden lg:block">
                            <x-dropdown-link class='hidden md:block' :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                                <span>
                                    <svg class="inline-block w-6 h-6 fill-current" fill="currentColor"
                                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                        </path>
                                    </svg>
                                </span>
                                {{ __('Dashboard') }}
                            </x-dropdown-link>

                            <x-dropdown-link class="hidden text-bold md:block" :href="route('admin.settings')" wire:navigate>
                                <span>
                                    <svg class="inline-block w-5 h-5 fill-current text-navy-blue"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="settings">
                                        <path fill="none" d="M0 0h24v24H0V0z"></path>
                                        <path
                                            d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z">
                                        </path>
                                    </svg>
                                </span>
                                {{ __('Settings') }}
                            </x-dropdown-link>

                            <!-- Logout Button -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link class='hidden md:block'>
                                    <span>
                                        <svg class='inline-block w-5 h-5 fill-current text-navy-blue'
                                            viewBox="0 0 24 24" width='10' height='10' fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                stroke-linejoin="round">
                                            </g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M17.2929 14.2929C16.9024 14.6834 16.9024 15.3166 17.2929 15.7071C17.6834 16.0976 18.3166 16.0976 18.7071 15.7071L21.6201 12.7941C21.6351 12.7791 21.6497 12.7637 21.6637 12.748C21.87 12.5648 22 12.2976 22 12C22 11.7024 21.87 11.4352 21.6637 11.252C21.6497 11.2363 21.6351 11.2209 21.6201 11.2059L18.7071 8.29289C18.3166 7.90237 17.6834 7.90237 17.2929 8.29289C16.9024 8.68342 16.9024 9.31658 17.2929 9.70711L18.5858 11H13C12.4477 11 12 11.4477 12 12C12 12.5523 12.4477 13 13 13H18.5858L17.2929 14.2929Z"
                                                    fill="navy-blue"></path>
                                                <path
                                                    d="M5 2C3.34315 2 2 3.34315 2 5V19C2 20.6569 3.34315 22 5 22H14.5C15.8807 22 17 20.8807 17 19.5V16.7326C16.8519 16.647 16.7125 16.5409 16.5858 16.4142C15.9314 15.7598 15.8253 14.7649 16.2674 14H13C11.8954 14 11 13.1046 11 12C11 10.8954 11.8954 10 13 10H16.2674C15.8253 9.23514 15.9314 8.24015 16.5858 7.58579C16.7125 7.4591 16.8519 7.35296 17 7.26738V4.5C17 3.11929 15.8807 2 14.5 2H5Z"
                                                    fill="navy-blue"></path>
                                            </g>
                                        </svg>
                                    </span>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>




            <!-- Hamburger -->
            <div class="flex items-center -me-2 sm:hidden">
                <button @click="open = ! open , more = false"
                    class="inline-flex items-center justify-center p-2 transition duration-150 ease-in-out rounded-md text-navy-blue hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 ">
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

    <div :class="{ 'block': open, 'hidden': !open }" class="fixed z-50 hidden w-screen h-10 sm:hidden">
        <div class="flex px-2 py-3 border-b-2 border-white bg-navy-blue">
            <x-avatar class="border-2 border-white" />
            <div class="px-4">
                <div class="text-base font-medium text-white" x-data="{{ json_encode(['name' => auth()->user()->firstname . ' ' . auth()->user()->lastname]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-gray-100">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="fixed w-screen pb-1 bg-white border-t border-gray-200 text-navy-blue">
            <div>
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                    <span>
                        <svg class="inline-block w-6 h-6 fill-navy-blue" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                    </span>
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link x-cloak="display:none" wire:click="$toggle('drawer')" wire:navigate>
                    <span>
                        <svg class="inline-block w-5 h-5 fill-navy-blue" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M19.3399 14.49L18.3399 12.83C18.1299 12.46 17.9399 11.76 17.9399 11.35V8.82C17.9399 6.47 16.5599 4.44 14.5699 3.49C14.0499 2.57 13.0899 2 11.9899 2C10.8999 2 9.91994 2.59 9.39994 3.52C7.44994 4.49 6.09994 6.5 6.09994 8.82V11.35C6.09994 11.76 5.90994 12.46 5.69994 12.82L4.68994 14.49C4.28994 15.16 4.19994 15.9 4.44994 16.58C4.68994 17.25 5.25994 17.77 5.99994 18.02C7.93994 18.68 9.97994 19 12.0199 19C14.0599 19 16.0999 18.68 18.0399 18.03C18.7399 17.8 19.2799 17.27 19.5399 16.58C19.7999 15.89 19.7299 15.13 19.3399 14.49Z">
                                </path>
                                <path
                                    d="M14.8297 20.01C14.4097 21.17 13.2997 22 11.9997 22C11.2097 22 10.4297 21.68 9.87969 21.11C9.55969 20.81 9.31969 20.41 9.17969 20C9.30969 20.02 9.43969 20.03 9.57969 20.05C9.80969 20.08 10.0497 20.11 10.2897 20.13C10.8597 20.18 11.4397 20.21 12.0197 20.21C12.5897 20.21 13.1597 20.18 13.7197 20.13C13.9297 20.11 14.1397 20.1 14.3397 20.07C14.4997 20.05 14.6597 20.03 14.8297 20.01Z">
                                </path>
                            </g>
                        </svg>
                        {{ __('Notification') }}
                        @if ($notification)
                            <span
                                class="inline-block w-5 h-5 text-sm font-bold text-center text-white rounded-full fill-current bg-navy-blue">
                                {{ $notifications->count() }}
                            </span>
                        @endif
                    </span </x-responsive-nav-link>

                    <x-mary-drawer wire:model='drawer' class="w-11/12 rounded-none">
                        <div class="bg-black">
                            <div
                                class="flex items-center justify-end px-4 py-2 text-sm font-bold bg-white border-b-2 border-gray-200 text-navy-blue">
                                <span>
                                    <x-mary-button icon="o-x-mark"
                                        class="btn-ghost btn-sm dark:focus:bg-white dark:active:bg-white"
                                        wire:click="closeDrawer" />
                                </span>
                            </div>
                            <div class="transition-all duration-500 bg-gray-200 " x-cloak="display:none">
                                <div class="w-full py-2 bg-white">
                                    @if ($notification)
                                        <div class="grid gap-2">
                                            @foreach ($notifications as $notification)
                                                <div class="flex justify-between w-full bg-white hover:bg-gray-100">
                                                    <div class="pt-1 mx-1">
                                                        <div class="text-sm">
                                                            <span class="font-medium text-slate-900">
                                                                {{ $notification->message }}

                                                            </span>

                                                            <div class="text-xs">
                                                                {{ Carbon::parse($notification->created_at)->format('D-M-Y') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <x-bladewind.button
                                                        wire:click="markAsRead('{{ $notification->id }}')"
                                                        class="ml-20 hover:bg-navy-blue" type="bg-golden"
                                                        button_text_css="text-white" size="small">
                                                        <span>
                                                            Mark As Read
                                                        </span>
                                                    </x-bladewind.button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="w-full bg-white hover:bg-gray-100">
                                            <div class="pt-1">
                                                <div class="text-sm font-extrabold">

                                                    <h3 class="w-full text-lg font-extrabold text-gray-700">No
                                                        Notifications
                                                    </h3>
                                                    <p class="w-full mt-1 text-sm text-gray-500">You're all
                                                        caught up!</p>

                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-mary-drawer>

                    <x-responsive-nav-link @click="more = !more" wire:navigate>

                        <span>
                            <svg class="inline-block w-5 h-5 fill-navy-blue" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M4 8C5.10457 8 6 7.10457 6 6C6 4.89543 5.10457 4 4 4C2.89543 4 2 4.89543 2 6C2 7.10457 2.89543 8 4 8Z">
                                    </path>
                                    <path
                                        d="M4 14C5.10457 14 6 13.1046 6 12C6 10.8954 5.10457 10 4 10C2.89543 10 2 10.8954 2 12C2 13.1046 2.89543 14 4 14Z">
                                    </path>
                                    <path
                                        d="M6 18C6 19.1046 5.10457 20 4 20C2.89543 20 2 19.1046 2 18C2 16.8954 2.89543 16 4 16C5.10457 16 6 16.8954 6 18Z">
                                    </path>
                                    <path
                                        d="M21 7.5C21.5523 7.5 22 7.05228 22 6.5V5.5C22 4.94772 21.5523 4.5 21 4.5H9C8.44772 4.5 8 4.94772 8 5.5V6.5C8 7.05228 8.44772 7.5 9 7.5H21Z">
                                    </path>
                                    <path
                                        d="M22 12.5C22 13.0523 21.5523 13.5 21 13.5H9C8.44772 13.5 8 13.0523 8 12.5V11.5C8 10.9477 8.44772 10.5 9 10.5H21C21.5523 10.5 22 10.9477 22 11.5V12.5Z">
                                    </path>
                                    <path
                                        d="M21 19.5C21.5523 19.5 22 19.0523 22 18.5V17.5C22 16.9477 21.5523 16.5 21 16.5H9C8.44772 16.5 8 16.9477 8 17.5V18.5C8 19.0523 8.44772 19.5 9 19.5H21Z">
                                    </path>
                                </g>
                            </svg>
                        </span>
                        {{ __('More') }}
                    </x-responsive-nav-link>
                    <div class="px-5 transition-all duration-500 bg-gray-200" x-show="more">
                        <x-responsive-nav-link :href="route('market.place')" wire:navigate>
                            <span>
                                <svg class="inline-block w-5 h-5 fill-current" fill="#000000" height="200px"
                                    width="200px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 511 511"
                                    xml:space="preserve">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                    </g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g>
                                            <path
                                                d="M503.5,440H479V207.433c13.842-3.487,24-16.502,24-31.933v-104c0-8.547-6.953-15.5-15.5-15.5h-464 C14.953,56,8,62.953,8,71.5v104c0,15.432,10.158,28.446,24,31.933V440H7.5c-4.142,0-7.5,3.358-7.5,7.5s3.358,7.5,7.5,7.5h496 c4.142,0,7.5-3.358,7.5-7.5S507.642,440,503.5,440z M488,71.5v104c0,9.383-6.999,17.384-15.602,17.834 c-4.595,0.235-8.939-1.36-12.254-4.505c-3.317-3.148-5.145-7.4-5.145-11.971V71h32.5C487.776,71,488,71.224,488,71.5z M71,71h33 v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M119,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5 s-16.5-7.402-16.5-16.5V71z M167,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M215,71h33v105.858 c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M263,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5 V71z M311,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M359,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5 s-16.5-7.402-16.5-16.5V71z M407,71h33v105.858c0,9.098-7.402,16.5-16.5,16.5s-16.5-7.402-16.5-16.5V71z M23,175.5v-104 c0-0.276,0.224-0.5,0.5-0.5H56v105.858c0,4.571-1.827,8.823-5.145,11.971c-3.314,3.146-7.663,4.743-12.254,4.505 C29.999,192.884,23,184.883,23,175.5z M47,207.462c5.266-1.279,10.128-3.907,14.181-7.753c0.822-0.78,1.599-1.603,2.326-2.462 c5.782,6.793,14.393,11.11,23.993,11.11c9.604,0,18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119 c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119 c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119 c5.782,6.799,14.396,11.119,24,11.119s18.218-4.32,24-11.119c5.782,6.799,14.396,11.119,24,11.119c9.6,0,18.21-4.317,23.993-11.11 c0.728,0.859,1.504,1.682,2.326,2.462c4.054,3.847,8.914,6.482,14.181,7.761V440h-33V263.5c0-8.547-6.953-15.5-15.5-15.5h-96 c-8.547,0-15.5,6.953-15.5,15.5V440H47V207.462z M416,440h-97V263.5c0-0.276,0.224-0.5,0.5-0.5h96c0.276,0,0.5,0.224,0.5,0.5V440z">
                                            </path>
                                            <path
                                                d="M343.5,336c-4.142,0-7.5,3.358-7.5,7.5v16c0,4.142,3.358,7.5,7.5,7.5s7.5-3.358,7.5-7.5v-16 C351,339.358,347.642,336,343.5,336z">
                                            </path>
                                            <path
                                                d="M262.5,248h-174c-4.687,0-8.5,3.813-8.5,8.5v142c0,4.687,3.813,8.5,8.5,8.5h174c4.687,0,8.5-3.813,8.5-8.5v-142 C271,251.813,267.187,248,262.5,248z M256,392H95V263h161V392z">
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </span>
                            {{ __('Marketplace') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('explore')" wire:navigate>
                            <span>
                                <svg class="inline-block w-6 h-6 fill-current text-navy-blue"
                                    xmlns="http://www.w3.org/2000/svg" id="explore">
                                    <path fill="none" d="M0 0h24v24H0V0z"></path>
                                    <path
                                        d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z">
                                    </path>
                                </svg>
                            </span>
                            {{ __('Explore') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('blog')" wire:navigate>
                            <span>

                                <span>
                                    <svg class="inline-block w-5 h-5 fill-current" id="Layer_1" data-name="Layer 1"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <defs>
                                            <style>
                                                .cls-1 {
                                                    fill: #141f38;
                                                }
                                            </style>
                                        </defs>
                                        <title>browser-3-glyph</title>
                                        <path class="cls-1"
                                            d="M448,0H64A64,64,0,0,0,0,64v51.2H512V64A64,64,0,0,0,448,0ZM70.4,76.8A19.2,19.2,0,1,1,89.6,57.6,19.2,19.2,0,0,1,70.4,76.8Zm51.2,0a19.2,19.2,0,1,1,19.2-19.2A19.2,19.2,0,0,1,121.6,76.8Zm51.2,0A19.2,19.2,0,1,1,192,57.6,19.2,19.2,0,0,1,172.8,76.8ZM0,448a64,64,0,0,0,64,64H448a64,64,0,0,0,64-64V140.8H0ZM294.4,192H435.2a12.8,12.8,0,1,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,51.2H435.2a12.8,12.8,0,1,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,51.2H384a12.8,12.8,0,0,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,76.8H435.2a12.8,12.8,0,1,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6Zm0,51.2H384a12.8,12.8,0,0,1,0,25.6H294.4a12.8,12.8,0,1,1,0-25.6ZM64,211.2a32,32,0,0,1,32-32H211.2a32,32,0,0,1,32,32V416a32,32,0,0,1-32,32H96a32,32,0,0,1-32-32Z" />
                                    </svg>
                                </span>
                                {{ __('Blog') }}

                        </x-responsive-nav-link>
                    </div>

                    <x-responsive-nav-link :href="route('admin.settings')" wire:navigate>
                        <span>
                            <svg class="inline-block w-5 h-5 fill-current text-navy-blue"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="settings">
                                <path fill="none" d="M0 0h24v24H0V0z"></path>
                                <path
                                    d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z">
                                </path>
                            </svg>
                        </span>
                        {{ __('Settings') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start text-navy-blue">
                        <x-responsive-nav-link>

                            <span class="">
                                <svg class='inline-block w-5 h-5 fill-navy-blue' viewBox="0 0 24 24" width='10'
                                    height='10' fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                    </g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path
                                            d="M17.2929 14.2929C16.9024 14.6834 16.9024 15.3166 17.2929 15.7071C17.6834 16.0976 18.3166 16.0976 18.7071 15.7071L21.6201 12.7941C21.6351 12.7791 21.6497 12.7637 21.6637 12.748C21.87 12.5648 22 12.2976 22 12C22 11.7024 21.87 11.4352 21.6637 11.252C21.6497 11.2363 21.6351 11.2209 21.6201 11.2059L18.7071 8.29289C18.3166 7.90237 17.6834 7.90237 17.2929 8.29289C16.9024 8.68342 16.9024 9.31658 17.2929 9.70711L18.5858 11H13C12.4477 11 12 11.4477 12 12C12 12.5523 12.4477 13 13 13H18.5858L17.2929 14.2929Z">
                                        </path>
                                        <path
                                            d="M5 2C3.34315 2 2 3.34315 2 5V19C2 20.6569 3.34315 22 5 22H14.5C15.8807 22 17 20.8807 17 19.5V16.7326C16.8519 16.647 16.7125 16.5409 16.5858 16.4142C15.9314 15.7598 15.8253 14.7649 16.2674 14H13C11.8954 14 11 13.1046 11 12C11 10.8954 11.8954 10 13 10H16.2674C15.8253 9.23514 15.9314 8.24015 16.5858 7.58579C16.7125 7.4591 16.8519 7.35296 17 7.26738V4.5C17 3.11929 15.8807 2 14.5 2H5Z">
                                        </path>
                                    </g>
                                </svg>
                            </span>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
            </div>
        </div>
    </div>
</nav>
