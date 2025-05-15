<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public string $google_id = '';
    public string $avatar = '';

    public function mount()
    {
        $this->firstname = strval(session('firstname'));
        $this->lastname = strval(session('lastname'));
        $this->email = strval(session('email'));
        $this->google_id = strval(session('google_id'));
        $this->avatar = strval(session('avatar'));
        $this->role = 'user';
    }

    /**
     * Handle an incoming registration request.
     */
    public function register()
    {
        $validated = $this->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(
            new Registered(
                ($user = User::create([
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'email' => $validated['email'],
                    'google_id' => $this->google_id,
                    'avatar' => $this->avatar,
                    'referral_link' => strtoupper(substr($validated['firstname'], 0, 2) . substr($validated['lastname'], 0, 2) . rand(1000, 9999)),
                    'notify_purchase' => $validated['role'] == 'creative' ? 'yes' : 'no',
                    'password' => $validated['password'],
                    'role' => $validated['role'],
                    'email_verified at' => now(),
                ])),
            ),
        );
        Auth::login($user);

        switch ($user->role) {
            case 'creative':
                session()->reflash();
                session()->put('user', $user);
                $this->redirect(route('creative.payment.preference', absolute: false));
                break;

            case 'user':
                $this->redirect(route('dashboard', absolute: false));
                break;
        }
    }
}; ?>

<div class="my-7 px-10">
    <h1 class="my-1 mb-5 text-4xl font-extrabold">Complete Registration</h1>
    <div>

        <div wire:loading class="py-3 mb-6 text-white transition-opacity duration-500 border rounded alert-info alert top-10 bg-navy-blue border-navy-blue"
            role="alert">
            <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>

        @if (session()->has('message'))
            <div class="fixed top-4 right-4 z-[9999] w-[90%] max-w-sm sm:max-w-xs md:max-w-sm lg:max-w-md xl:max-w-lg"
                x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform" x-transition:leave-end="opacity-0 -translate-y-2">
                <div
                    class="flex items-center justify-between p-4 text-sm font-semibold text-white bg-green-600 rounded-xl shadow-lg">
                    <span>{{ session('message') }}</span>
                    <button @click="show = false" class="ml-4 focus:outline-none">
                        @svg('eva-close', 'w-5 h-5 text-white hover:text-red-200')
                    </button>
                </div>
            </div>
        @endif

    </div>
    <form wire:submit="register" enctype="multipart/form-data">

        {{-- role selector button --}}

        <div class="">
            {{-- role selector button --}}
            <div class="w-full justify-evenly md:flex">
                <div class="w-full text-lg font-bold md:w-1/2 pr-7">
                    Complete your registration to access your Twellr account
                </div>
                <x-selector />

            </div>
            <x-input-error :messages="$errors->get('role')" class="z-50 mt-2" />


            {{-- First and Last Name --}}
            <div class="flex w-full justify-evenly">
                <!-- First Name -->
                <div class="w-1/2 pr-2 ">
                    <x-input-label class="absolute z-50 px-1 mt-3 ml-3 font-extrabold bg-white" for="firstname"
                        :value="__('First Name')" />

                    <div class="relative mt-4">
                        <x-text-input wire:model="firstname" id="firstname" class="block w-full mt-5 border-5"
                            type="text" name="firstname" required autofocus autocomplete="firstname" />
                        <x-input-error :messages="$errors->get('firstname')" class="z-50 mt-2" />
                    </div>
                </div>

                <!-- Last Name -->
                <div class="w-1/2 ml-2 ">
                    <x-input-label class="absolute z-50 px-1 mt-3 ml-3 font-extrabold bg-white" for="lastname"
                        :value="__('Last Name')" />

                    <div class="relative mt-4">
                        <x-text-input wire:model="lastname" id="lastname" class="block w-full mt-5" type="text"
                            name="lastname" required autofocus autocomplete="lastname" />
                        <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Email Address -->
            <div class="relative pr-2 mt-2 w-100">
                <x-input-label class="absolute z-50 px-1 mt-4 ml-3 font-extrabold bg-white" for="email"
                    :value="__('Email')" />

                <div class="absolute w-full mt-1">
                    <x-text-input wire:model="email" id="email" class="block w-full mt-5" type="email"
                        name="email" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>

            <!-- Password -->
            <div class="relative pt-1 pr-2 mt-24 w-100">
                <x-input-label class="absolute z-50 px-1 mt-2 ml-3 font-extrabold bg-white" for="password"
                    :value="__('Password')" />

                <div class="absolute w-full mt-1 ">
                    <x-text-input wire:model="password" id="password" class="block w-full mt-3 " type="password"
                        name="password" required autocomplete="password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="relative block mt-10">
            <div>
                <x-form-action-button :value="__('Create Account')" />


            </div>
        </div>
    </form>


</div>
