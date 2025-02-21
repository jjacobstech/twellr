<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{


    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public string $google_id='';
    public string $avatar='';

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
            'role' => ['required','string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);


        event(new Registered($user = User::create(
            [
                'name'=>$validated['firstname'].' '.$validated['lastname'],
                'email' => $validated['email'],
                'google_id' => $this->google_id,
                'avatar' => $this->avatar,
                'password' => $validated['password'],
                'role' => $validated['role'],
                'email_verified at' => now()
            ]
        )));

        Auth::login($user);

        session()->flash('message', 'Registration successful');

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="my-7">
    <h1 class="my-1 mb-5 text-4xl font-extrabold">Complete Registration</h1>
    <div>

        @if (session()->has('message'))

        <div class="alert alert-success">

            {{ session('message') }}

        </div>

        @endif
    </div>
    <form wire:submit="register" enctype="multipart/form-data">

        {{-- role selector button --}}

        <div class="">
            <div class="flex justify-evenly">
                <div class="w-1/2 pr-10 text-lg font-bold capitalize">
                    sign up to create an account on twellr
                </div>
                <ul class="flex w-1/2 font-bold">
                    <li class="w-1/2 h-5">
                        <input type="radio" wire:model='role' id="creative" name="role" value="creative"
                            class="hidden peer" required />
                        <label for="creative"
                            class="block w-full py-1 text-center bg-white border rounded-l-lg cursor-pointer px-9 text-navy-blue border-navy-blue peer-checked:border-navy-blue peer-checked:text-white peer-checked:bg-navy-blue">

                            {{ __('Signup as') }} <br> {{
                            __('Creative') }}

                        </label>
                    </li>
                    <li class="w-1/2">
                        <input type="radio" wire:model='role' id="user" name="role" checked value="user"
                            class="hidden peer">
                        <label for="user"
                            class="block w-full py-1 text-center bg-white border rounded-r-lg cursor-pointer px-9 text-navy-blue border-navy-blue peer-checked:border-navy-blue peer-checked:text-white <x-input-error :messages="
                            $errors->get('role')" class="z-50 mt-2" />peer-checked:bg-navy-blue">
                            {{ __('Signup as') }} <br> {{__('User') }}

                        </label>
                    </li>
                </ul>

            </div>

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
                    <x-text-input wire:model="email" id="email" class="block w-full mt-5" type="email" name="email"
                        required autofocus autocomplete="username" />
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
            <x-input-error :messages="$errors->get('role')" class="z-50 mt-2" />
        </div>
    </form>
</div>
