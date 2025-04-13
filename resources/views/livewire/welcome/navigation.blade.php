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
    public bool $drawer = false;
    public $result;
    public $userActions;
    public $keyword = '';

    public function search($keyword = '')
    {
        // Example of security concern
        // Guests can not search
        if ($keyword != '') {
            if (!Auth::user()) {
                $this->result = $this->users($keyword);
                $this->userActions = $this->actions();
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
        $register ="<x-mary-icon name='o-user' class='p-2 rounded-full w-11 h-11 bg-yellow-50' />";
        $explore = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="cuentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5m0-10v10"/></svg>';

        $blog = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3V3z"/><path d="M3 9h18M3 15h18"/></svg>';

        $marketplace ='<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5m0-10v10"/></svg>';

          $support ='<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5m0-10v10"/></svg>';

        return [
             [
                'name' => 'Register',
                'description' => 'Create A Twellr Account',
                'icon' => $register,
                'link' => route('register'),
            ],
             [
                'name' => 'Explore',
                'description' => 'Discover New Creatives',
                'icon' => $explore,
                'link' => route('explore'),
            ],
             [
                'name' => 'Blog',
                'description' => 'Read The Latest News',
                'icon' => $blog,
                'link' => route('blog'),
            ],
           [
                'name' => 'Marketplace',
                'description' => 'Buy & Sell Designs',
                'icon' => $marketplace,
                'link' => route('market.place'),
            ],
            [
                'name' => 'Support',
                'description' => 'Get Help & Support',
                'icon' => $support,
                'link' => route('support'),
            ],
        ];
    }
};
?>


<nav x-data="{ open: false, more: false, notification: false,term:'' }" class="bg-white border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="px-5 mx-auto md:px-2 max-w-7xl sm:px-6 lg:px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a class="flex" href="{{ route('home') }} " wire:navigate>
                        <x-application-logo class="block w-auto fill-current h-9" />
                        <img class="hidden md:h-5 md:px-3 md:my-1 md:block" src="{{ asset('assets/twellr-text.png') }}"
                            alt="">
                    </a>
                </div>
            </div>

            <div class="w-full sm:-my-px sm:ms-10 sm:flex">
                {{-- Search Bar --}}
                <form class="w-full px-3 sm:px-5 md:px-9">
                    <div class="flex justify-between w-full">
                        <div class="relative flex w-full py-2 md:py-3">
                            <input type="text" id="search-dropdown" x-model="term" x-on:keydown="$wire.search(term)"
                                x-on:keyup="$wire.search(term)"
                                class="font-bold block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-200 rounded-l-lg border-0 active:border-0 hover:border hover:border-gray-400 focus:border-0 focus:ring-0 border-navy-blue "
                                placeholder="Search by: Creator, Design, Location, Ratings"
                                alt="Search by: Creator, Design, Location, Ratings"
                                title='Search by: Creator, Design, Location, Ratings' />
                            <span wire:click="search"
                                class="top-0 end-0 p-2.5 text-sm font-medium h-full text-white bg-gray-200 border-0 rounded-e-lg active:bg-white active:text-navy-blue border-navy-blue hover:bg-navy-blue focus:ring-0 focus:outline-none ">
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

                                        @if ($userActions != '')
                                            {{-- Actions --}}
                                            <div class="w-full p-2 sm:p-3">
                                                <div class="flex flex-col gap-1 sm:gap-2">
                                                    @foreach ($userActions as $action)
                                                        <a href="{{ $action['link'] }}"
                                                            class="flex items-center gap-2 p-1 sm:p-2 border-b border-gray-200 hover:bg-gray-100 transition-colors">
                                                          <div>
                                                            <x-mary-icon name='o-user' class='p-2 rounded-full w-11 h-11 bg-black' />
                                                          </div>
                                                            <div class="min-w-0 flex-1">
                                                                <h4
                                                                    class="text-xs sm:text-sm font-bold text-gray-800 truncate">
                                                                    {{ $action['name'] }}</h4>
                                                                <p class="text-xs text-gray-500 truncate">
                                                                    {{ $action['description'] }}</p>
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>

                                        @endif

                                        {{-- Users result --}}
                                        <div class="w-full p-2 sm:p-3">
                                            <div class="flex flex-col gap-1 sm:gap-2">
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
            </div>

        </div>
    </div>
</nav>
