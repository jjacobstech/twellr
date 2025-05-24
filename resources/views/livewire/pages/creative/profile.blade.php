<?php

use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Product;
use App\Models\Referral;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use function Livewire\Volt\{layout, state, mount, uses, action};

layout('layouts.app');
uses(Toast::class);

state([
    'user' => fn() => User::where('referral_link', '=', request()->creator)->first(),
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'rating' => '',
    'referral_link' => '',
    'designer' => '',
    'designs' => [],
    'isFollowing' => false,
    'statuses' => [
        '1' => 'Rookie',
        '2' => 'Notched',
        '3' => 'Dazzler',
        '4' => 'Boss',
        '5' => 'Jagaban',
    ]
]);

/**
 * Mount the component.
 */
mount(function () {
    if (!request()->has('creator')) {
        return redirect()->route('dashboard');
    }

    if(Auth::id() === $this->user->id) {
        return redirect()->route('dashboard');
    }
    $this->designs = Product::where('user_id', $this->user->id)->get();

    // Check if the current user is following this creator
    if (Auth::check()) {
        $this->isFollowing = Auth::user()->following()->where('following_id', $this->user->id)->exists();
    }
    $this->firstname = $this->user->firstname;
    $this->lastname = $this->user->lastname;
    $this->email = $this->user->email;
    $this->rating = $this->user->rating;
    $this->referral_link = config('app.url') . '/r/' . $this->user->referral_link;
    $this->designer = $this->user->id;
});

/**
 * Toggle follow status for this creator
 */
$toggleFollow = action(function () {
    if (!Auth::check()) {
        $this->warning('Please login to follow creators');
        return redirect()->route('login');
    }

    $currentUser = Auth::user();

    if ($currentUser->id === $this->user->id) {
        $this->warning('You cannot follow yourself');
        return;
    }

    if ($this->isFollowing) {
        // Unfollow
        $currentUser->following()->detach($this->user->id);
        $this->isFollowing = false;
        $this->success('You have unfollowed ' . $this->user->firstname);
    } else {
        // Follow
        $currentUser->following()->attach($this->user->id);
        $this->isFollowing = true;
        $this->success('You are now following ' . $this->user->firstname);
    }
});

?>
<div class="w-[100%] overflow-y-scroll px-8 md:px-16 pb-20 h-screen bg-gray-100 scrollbar-none">

    <div class="w-full">
        <div style="background-image: url('@if (empty($user->cover)) {{ asset('assets/pexels-solliefoto-298863.jpg') }}@else{{ asset('uploads/cover/' . $user->cover) }} @endif')"
            class="rounded-[14px] bg-no-repeat bg-cover justify-center items-center w-full min-h-[250px]">
            <div class="relative flex justify-center w-full bg-white">
                <img class="w-20 h-20 md:w-40 md:h-40 rounded-full absolute md:mt-28 z-50 mt-14 border-[#bebebe] border-[1px] object-cover"
                    src="@if (empty($user->avatar)) {{ asset('assets/icons-user.png') }}
                        @else
                        {{ asset('uploads/avatar/' . $user->avatar) }} @endif"
                    alt="{{ $user->name }}" />
            </div>
            <div
                class="mt-24 md:py-9 w-100 bg-white rounded-[14px] md:mt-[12rem] text-center items-center justify-center grid">
               <div class="mt-20  md:mt-16 flex flex-col items-center justify-center space-y-2">
                        <x-bladewind.rating rating="{{ $rating }}" size="medium" class="text-golden"
                            name="creative-rating" />

                        <span
                            class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-navy-blue text-white text-center">
                            @switch($rating)
                                @case(1)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(2)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(3)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(4)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(5)
                                    {{ $statuses[$rating] }}
                                @break

                                @default
                            @endswitch
                        </span>
                    </div>

                <h1 class="my-2 text-lg font-bold text-gray-500 md:text-3xl">{{ $user->firstname }}
                    {{ $user->lastname }}</h1>

                <!-- Follow Button -->
                @if (Auth::check() && Auth::id() !== $user->id)
                    <div class="mt-2 mb-4">
                        <button wire:click="toggleFollow"
                            class="px-6 py-2 transition-all duration-300 rounded-full {{ $isFollowing ? 'bg-gray-200 text-gray-700' : 'bg-golden text-white' }} hover:opacity-90">
                            {{ $isFollowing ? 'Following' : 'Follow' }}
                        </button>
                    </div>
                @endif
            </div>
        </div>


        <div
            class="justify-between px-5 py-10 my-10 bg-white shadow-sm md:px-10 md:gap-6 md:flex w-100 rounded-xl scrollbar-none">
            <div x-cloak="display:hidden"
                class="relative grid w-full h-full gap-5 px-5 pt-1 mb-1 overflow-y-scroll md:h-screen lg:hidden md:grid-cols-4 sm:grid-cols-4 scrollbar-none">
                @forelse ($designs as $design)
                    <a href="{{ route('market.place') }}"
                        class="w-full  max-w-sm md:overflow-hidden transition-shadow duration-300 shadow-md rounded-xl hover:shadow-lg aspect-square">
                        <!-- Product Image Container with fixed aspect ratio -->
                        <div class="relative group aspect-square">
                            <img class="object-cover aspect-square w-full h-full rounded-xl lg:rounded-t-none"
                                src="{{ asset('uploads/products/design-stack/' . $design->front_view) }}"
                                alt="{{ $design->name }}">
                        </div>
                    </a>

                @empty
                    <h1 class="text-gray-500">No Designs Available</h1>
                @endforelse
            </div>

            <div x-cloak="display:hidden"
                class="relative hidden w-full gap-5 px-5 pt-1 lg:overflow-y-scroll lg:grid md:grid-cols-8 mb-[115px] scrollbar-none">
                @forelse ($designs as $design)
                    <div
                        class="w-full  max-w-sm md:overflow-hidden transition-shadow duration-300 shadow-md rounded-xl hover:shadow-lg aspect-square">
                        <!-- Product Image Container with fixed aspect ratio -->
                        <a href="{{ route('market.place') }}"
                            class="relative group aspect-square">
                            <img class="object-cover aspect-square w-full h-full rounded-xl lg:rounded-t-none"
                                src="{{ asset('uploads/products/design-stack/' . $design->front_view) }}"
                                alt="{{ $design->name }}">
                        </a>
                    </div>
                @empty
                    <h1 class="text-gray-500">No Designs Available</h1>
                @endforelse
            </div>
        </div>
    </div>

</div>
