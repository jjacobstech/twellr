<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public int $rating;
    public ?string $referral_link = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->firstname = Auth::user()->firstname;
        $this->lastname = Auth::user()->lastname;
        $this->email = Auth::user()->email;
        $this->referral_link = Auth::user()->referral_link;
        $this->rating = Auth::user()->rating;
    }
}; ?>

<div class="w-[100%] overflow-y-scroll px-8 md:px-16 mb-20 ">
    <header class="flex items-center justify-between w-full">
        <h2 class="py-4 text-4xl font-extrabold text-gray-500">
            {{ __('Profile') }}
        </h2>
        <a href="{{ route('settings') }}">
            <svg class="h-7 w-7 fill-navy-blue" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                <path
                    d="M402.6 83.2l90.2 90.2c3.8 3.8 3.8 10 0 13.8L274.4 405.6l-92.8 10.3c-12.4 1.4-22.9-9.1-21.5-21.5l10.3-92.8L388.8 83.2c3.8-3.8 10-3.8 13.8 0zm162-22.9l-48.8-48.8c-15.2-15.2-39.9-15.2-55.2 0l-35.4 35.4c-3.8 3.8-3.8 10 0 13.8l90.2 90.2c3.8 3.8 10 3.8 13.8 0l35.4-35.4c15.2-15.3 15.2-40 0-55.2zM384 346.2V448H64V128h229.8c3.2 0 6.2-1.3 8.5-3.5l40-40c7.6-7.6 2.2-20.5-8.5-20.5H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V306.2c0-10.7-12.9-16-20.5-8.5l-40 40c-2.2 2.3-3.5 5.3-3.5 8.5z" />
            </svg>
        </a>
    </header>
    <div class="w-full ">

        <div
            style="background-image: url('@if (empty(Auth::user()->cover)) {{ asset('assets/pexels-solliefoto-298863.jpg') }}@else{{ asset('uploads/cover/' . Auth::user()->cover) }} @endif')"class="rounded-[14px] bg-no-repeat bg-cover justify-center items-center w-full">
            <div class="relative flex justify-center w-full">
                <img class="w-20 h-20 md:w-40 md:h-40 rounded-full absolute md:mt-28 z-10 mt-14 border-[#bebebe] border-[1px]"
                    src="@if (empty(Auth::user()->avatar)) {{ asset('assets/icons-user.png') }}
                        @else
                        {{ asset('uploads/avatar/' . Auth::user()->avatar) }} @endif
                    "
                    alt="{{ Auth::user()->name }}" />
            </div>
            <div
                class="mt-24 md:py-9 w-100 bg-gray-100 rounded-[14px] md:mt-[12rem] text-center items-center justify-center grid">


                <span class="mt-12 md:mt-16">
                    <x-bladewind.rating rating="{{ $rating }}" size="medium" class="text-golden"
                        name="creative-rating" />
                </span>
                <h1 class="my-2 text-lg font-bold text-gray-500 md:text-3xl">{{ Auth::user()->firstname }}
                    {{ Auth::user()->lastname }}</h1>
            </div>
        </div>
        <div
            class="justify-between px-5 py-10 my-10 text-center bg-gray-100 md:px-10 md:gap-0 md:flex w-100 rounded-xl">
            <div class="grid justify-center gap-2 w-100 md:w-1/2 md:justify-items-start">
                <h1 class="text-4xl font-extrabold text-center text-gray-500 w-100">Refer and Earn</h1>
                <p class="text-xl font-bold text-gray-500 md:text-left w-100">Share your link with friends and earn
                    rewards</p>
            </div>
            <div class="justify-center gap-3 mt-3 md:mt-0 md:grid md:w-1/2 md:justify-items-start w-100">
                <p class="mb-2 text-xl font-bold text-gray-500 md:mb-0">Your referral link</p>
                <div class="flex w-full">
                    <input class="rounded-l-3xl border-0 px-5 w-[100%] text-gray-500" disabled type="text"
                        value="{{ $referral_link }}" name="" id="referral_link">
                    <button
                        class="px-6 py-3 text-xl font-extrabold bg-golden text-neutral-600 rounded-r-3xl active:bg-navy-blue active:text-white"
                        type="copy" @click="copyContent()">Copy</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let text = document.getElementById('referral_link').value;
    const copyContent = async () => {
        try {
            await navigator.clipboard.writeText(text);
            console.log('Content copied to clipboard');
            console.log(text);
        } catch (err) {
            console.error('Failed to copy: ', err);
        }
    }
</script>
