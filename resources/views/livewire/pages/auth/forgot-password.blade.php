<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="mt-20 lg:px-10 h-screen">
    <div class=" text-md text-gray-900 font-extrabold">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

@session('status')
    <div
        class="fixed top-4 right-4 z-[9999] w-[90%] max-w-sm sm:max-w-xs md:max-w-sm lg:max-w-md xl:max-w-lg"
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform"
        x-transition:leave-end="opacity-0 -translate-y-2"
    >
        <div class="flex items-center justify-between p-4 text-sm font-semibold text-white bg-navy-blue rounded-xl shadow-lg">
            <span>Message sent successfully.</span>
            <button @click="show = false" class="ml-4 focus:outline-none">
                @svg('eva-close', 'w-5 h-5 text-red-400 hover:text-red-500')
            </button>
        </div>
    </div>
@endSession


    <form wire:submit="sendPasswordResetLink">
        <!-- Email Address -->
        <div class="relative w-100">
            <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white " for="email" :value="__('Email')" />

            <div class="absolute w-full ">
                <x-text-input wire:model="email" id="email" class="block w-full mt-2" type="email" name="email"
                    required autofocus autocomplete="email" />
                <x-input-error :messages="$errors->get('email')" class="mx-2 absolute" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-7 pt-5 w-full font-extrabold ">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
    <div class="flex items-center justify-end mt-1 md:mt-10">
        <a class=" font-bold text-lg text-black underline rounded-md hover:text-navy-blue dark:hover:text-gray-100 focus:outline-none focus:text-black "
            href="{{ route('login') }}">
            {{ __('Back') }}
        </a>


    </div>
</div>
