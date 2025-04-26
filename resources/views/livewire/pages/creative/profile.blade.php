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
    'user' => fn() => User::where('referral_link','=',request()->creator)->first(),
    'firstname' => User::where('referral_link','=',request()->creator)->value('firstname'),
    'lastname' => User::where('referral_link','=',request()->creator)->value('lastname'),
    'email' => User::where('referral_link','=',request()->creator)->value('email'),
    'rating' => User::where('referral_link','=',request()->creator)->value('rating'),
    'referral_link' => config('app.url')."/r/".User::where('referral_link','=',request()->creator)->value('referral_link'),
    'designs' => [],
    'isFollowing' => false,
]);

/**
 * Mount the component.
 */
mount(function ()
{
    $this->designs = Product::where('user_id', $this->user->id)->get();

    // Check if the current user is following this creator
    if (Auth::check()) {
        $this->isFollowing = Auth::user()->following()->where('following_id', $this->user->id)->exists();
    }
});

$setRating = function($a, $b){
dd($b);
};

/**
 * Toggle follow status for this creator
 */
$toggleFollow = action( function() {
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
<div class="w-[100%] overflow-y-scroll px-8 md:px-16 mb-20 h-screen bg-gray-100">
    <header class="flex items-center justify-between w-full">
        <h2 class="py-4 text-4xl font-extrabold text-gray-500">
            {{ __('Profile') }}
        </h2>

        <a href="{{ route('settings') }}">
            <svg class="h-7 w-7 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </a>
    </header>
    <div class="w-full">
        <div style="background-image: url('@if (empty($user->cover)) {{ asset('assets/pexels-solliefoto-298863.jpg') }}@else{{ asset('uploads/cover/' . $user->cover) }} @endif')"
            class="rounded-[14px] bg-no-repeat bg-cover justify-center items-center w-full min-h-[250px]">
            <div class="relative flex justify-center w-full bg-white">
                <img class="w-20 h-20 md:w-40 md:h-40 rounded-full absolute md:mt-28 z-10 mt-14 border-[#bebebe] border-[1px] object-cover"
                    src="@if (empty($user->avatar)) {{ asset('assets/icons-user.png') }}
                        @else
                        {{ asset('uploads/avatar/' . $user->avatar) }} @endif"
                    alt="{{ $user->name }}" />
            </div>
            <div
                class="mt-24 md:py-9 w-100 bg-white rounded-[14px] md:mt-[12rem] text-center items-center justify-center grid">
                <span class="mt-12 md:mt-16">
                    <x-bladewind.rating rating="{{ $rating }}" name="{{ $user->id }}" size="medium" class="text-golden"
                        name="creative-rating" />
                </span>
                <h1 class="my-2 text-lg font-bold text-gray-500 md:text-3xl">{{ $user->firstname }}
                    {{ $user->lastname }}</h1>

                <!-- Follow Button -->
                @if (Auth::check() && Auth::id() !== $user->id)
                <div class="mt-2 mb-4">
                    <button
                        wire:click="toggleFollow"
                        class="px-6 py-2 transition-all duration-300 rounded-full {{ $isFollowing ? 'bg-gray-200 text-gray-700' : 'bg-golden text-white' }} hover:opacity-90">
                        {{ $isFollowing ? 'Following' : 'Follow' }}
                    </button>
                </div>
                @endif
            </div>
        </div>


        <div class="justify-between px-5 py-10 my-10 bg-white md:px-10 md:gap-6 md:flex w-100 rounded-xl shadow-sm">
            <div x-cloak="display:hidden"
                class="relative grid w-full h-full gap-5 px-5 pt-1 mb-1 overflow-y-scroll md:h-screen lg:hidden md:grid-cols-2 sm:grid-cols-2">
                @forelse ($designs as $design)
                    <x-product-card :product="$design" />
                @empty
                    <h1 class="text-gray-500">No Designs Available</h1>
                @endforelse
            </div>

            <div x-cloak="display:hidden"
                class="relative hidden w-full lg:h-screen gap-5 px-5 pt-1 lg:overflow-y-scroll lg:grid md:grid-cols-4 mb-[115px]">
                @forelse ($designs as $design)
                    <x-product-card :product="$design" />
                @empty
                    <h1 class="text-gray-500">No Designs Available</h1>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function copyReferralLink() {
        const copyText = document.getElementById("referral_link");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(copyText.value).then(() => {
            // Visual feedback
            const copyButton = document.getElementById("copyButton");
            const originalText = copyButton.innerText;
            const originalBg = copyButton.classList.contains('bg-golden');

            copyButton.innerText = "Copied!";
            copyButton.classList.remove('bg-golden');
            copyButton.classList.add('bg-green-500');
            copyButton.classList.add('text-white');

            setTimeout(() => {
                copyButton.innerText = originalText;
                copyButton.classList.remove('bg-green-500');
                copyButton.classList.remove('text-white');
                if (originalBg) copyButton.classList.add('bg-golden');
            }, 2000);
        });
    }
</script>
