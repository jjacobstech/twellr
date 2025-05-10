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
                    'link' => "/$user->firstname$user->lastname?creator=$user->referral_link",
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


<nav x-data="{ open: false, term: '' }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo -->
            <div class="flex items-center shrink-0">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center">
                    <x-application-logo class="h-9 w-auto fill-current" />
                    <img src="{{ asset('assets/twellr-text.png') }}" alt="Twellr" class="hidden md:block h-5 px-3" />
                </a>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 ml-6">
                <form class="relative">
                    <input
                        type="text"
                        x-model="term"
                        x-on:keydown="$wire.search(term)"
                        x-on:keyup="$wire.search(term)"
                        placeholder="Search by: Creator, Design, Location, Ratings"
                        title="Search by: Creator, Design, Location, Ratings"
                        class="w-full px-4 py-2.5 text-sm font-bold text-gray-900 bg-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                    />

                    <!-- Search Icon -->
                    <span class="absolute right-3 top-3 z-50 bg-gray-200 pl-2">
                        <svg class="w-4 h-5 text-[#fbaa0d]" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 20 20" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </span>

                    <!-- Search Results -->
                    @if ($result)
                        <div
                            x-show="term !== ''"
                            x-transition
                            class="absolute z-50 w-full mt-1 overflow-y-auto bg-white rounded-md shadow-lg max-h-96 scrollbar-none"
                        >
                            {{-- Static Actions --}}
                            @if ($userActions)
                                <div class="p-3 border-b border-gray-200">
                                    <div class="space-y-2">
                                        @foreach ($userActions as $action)
                                            <a href="{{ $action['link'] }}"
                                                class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-100 transition">
                                                <x-mary-icon name="o-user" class="w-11 h-11 p-2 bg-black rounded-full text-white" />
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-800">
                                                        {{ $action['name'] }}
                                                    </h4>
                                                    <p class="text-xs text-gray-500">{{ $action['description'] }}</p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- User Results --}}
                            <div class="p-3">
                                <div class="space-y-2">
                                    @foreach ($result as $item)
                                        <a href="{{ $item->link }}"
                                            class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-100 transition">
                                            <img src="{{ $item->avatar }}" alt="{{ $item->name }}"
                                                class="w-10 h-10 rounded-full object-cover" />
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-800">{{ $item->name }}</h4>
                                                <p class="text-xs text-gray-500">{{ $item->email }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</nav>

