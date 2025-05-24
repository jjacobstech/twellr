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
        '0' => 'Newbie',
        '1' => 'Rookie',
        '2' => 'Notched',
        '3' => 'Dazzler',
        '4' => 'Boss',
        '5' => 'Jagaban',
    ],
]);

/**
 * Mount the component.
 */
mount(function () {
    if (!request()->has('creator')) {
        return redirect()->route('dashboard');
    }

    if (Auth::id() === $this->user->id) {
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
    <!-- Profile Cover Section -->
    <div class="w-full ">
        <div style="background-image: url('@if (empty($user->cover)) {{ asset('assets/pexels-solliefoto-298863.jpg') }}@else{{ asset('uploads/cover/' . $user->cover) }} @endif')"
            class="rounded-[14px] bg-no-repeat bg-cover bg-center justify-center items-center w-full
                   mt-4 sm:mt-8 md:mt-12 lg:mt-16 xl:mt-20
                   min-h-[180px] sm:min-h-[200px] md:min-h-[220px] lg:min-h-[250px] xl:min-h-[280px] pt-1">

            <div class="relative flex justify-center w-full">
                <img class="absolute z-10 object-cover {{ !empty($user->avatar) && str_contains($user->avatar, 'https://') ? 'aspect-square' : '' }}
                         w-16 h-16 mt-10
                         sm:w-18 sm:h-18 sm:mt-12
                         md:w-20 md:h-20 md:mt-16
                         lg:w-32 lg:h-32 lg:mt-20
                         xl:w-40 xl:h-40 xl:mt-28
                         rounded-full border-2 sm:border-3 md:border-4 border-white shadow-lg"
                    src="@if (!empty($user->avatar) && str_contains($user->avatar, 'https://')) {{ $user->avatar }}@elseif(!empty($user->avatar)){{ asset('uploads/avatar/' . $user->avatar) }}@else{{ asset('assets/icons-user.png') }} @endif"
                    alt="{{ $user->name }}" />
            </div>

            <div
                class="w-full bg-white h-full rounded-[14px] border-0 text-center items-center justify-center grid
                      mt-20 px-4
                      sm:mt-20 sm:py-6 sm:px-6
                      md:mt-24 md:py-8 md:px-8
                      lg:mt-28 lg:py-9 lg:px-10
                      xl:mt-48 xl:py-9 xl:px-12">

              @if (Auth::check() && Auth::id() !== $user->id)
                    <div
                        class="grid items-center justify-center
                                sm:mt-8 md:mt-10 lg:mt-12 xl:mt-16 space-y-3">
                        <x-bladewind.rating rating="{{ $rating }}" size="small"
                            class="text-golden text-sm sm:text-base md:text-lg lg:text-xl" name="creative-rating" />

                        <span
                            class="inline-flex justify-center text-center text-white rounded-full bg-navy-blue font-semibold
                                   px-2 py-1 text-xs leading-4
                                   sm:px-3 sm:text-sm sm:leading-5
                                   md:px-3 md:text-sm md:leading-5
                                   lg:px-4 lg:text-base lg:leading-6">
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
                                    {{ $statuses[$rating] }}
                            @endswitch
                        </span>
                    </div>
                    <h1
                        class="font-bold text-gray-500 leading-tight break-words px-2
                             my-2 text-sm sm:text-base md:text-lg lg:text-2xl xl:text-3xl">
                        {{ $user->firstname }} {{ $user->lastname }}
                    </h1>

                @endif

            </div>
        </div>
            @if (Auth::check() && Auth::id() !== $user->id)
                    <div class="mt-2 mb-4 w-full flex justify-center">
                        <button wire:click="toggleFollow"
                            class="px-6 py-2 transition-all duration-300 rounded-full {{ $isFollowing ? 'bg-gray-200 text-gray-700' : 'bg-golden text-white' }} hover:opacity-90">
                            {{ $isFollowing ? 'Following' : 'Follow' }}
                        </button>
                    </div>
                @endif
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
                    <a href="{{ route('market.place') }}" class="relative group aspect-square">
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
