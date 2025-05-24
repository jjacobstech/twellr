<?php

use App\Models\User;
use App\Rules\RolesRule;
use Tzsk\Otp\Facades\Otp;
use Livewire\Volt\Component;
use App\Mail\EmailVerification;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;
    public string $role = 'user';

    /**
     * Handle an incoming authentication request.
     */
    public function login()
    {
        $this->validate();
        $validated = $this->validate([
            'role' => ['required', 'string', 'max:255', new RolesRule($this->form->email)],
        ]);
        $emailVerified = User::where('email', '=', $this->form->email)->value('email_verified_at');

        if (is_null($emailVerified)) {
            $user = User::where('email', '=', $this->form->email)->first();

            $otp = Otp::generate($this->form->email);

            try {
                // Send email for OTP verification
                Mail::to($this->form->email)->send(new EmailVerification($otp, $user->name));

                Session::put([
                    'user' => $user,
                    'secret' => $this->form->email,
                ]);
                // Redirect to the email verification page with user and secret
                return $this->redirect(route('email.verification'));
            } catch (\Exception $e) {
                // Optionally, log the exception for debugging purposes
                report($e); // This will log the error to the Laravel logs

                // Optionally, you can set a session variable with the error message if you want to persist the message
                session()->flash('otp', 'There was an error sending the email. We are working on it.');
            }
        } elseif (!empty($emailVerified)) {
            $this->form->authenticate();

            Session::regenerate(auth()->id());

            session()->flash('status', 'Login successful');

            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        }
    }
}; ?>

<div class="my-7 lg:px-10 h-screen">
    <div wire:loading
        class="py-3 mb-6 text-white transition-opacity duration-500 border rounded alert-info alert top-5 right-1 bg-navy-blue border-navy-blue absolute"
        role="alert">
        <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        Loading . . .
    </div>

    @session('status')
        <div class="fixed top-4 right-4 z-[9999] w-[90%] max-w-sm sm:max-w-xs md:max-w-sm lg:max-w-md xl:max-w-lg"
            x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform" x-transition:leave-end="opacity-0 -translate-y-2">
            <div
                class="flex items-center justify-between p-4 text-sm font-semibold text-white bg-navy-blue rounded-xl shadow-lg">
                <span>{{ session('status') }}</span>
                <button @click="show = false" class="ml-4 focus:outline-none">
                    @svg('eva-close', 'w-5 h-5 text-red-400 hover:text-red-500')
                </button>
            </div>
        </div>
    @endsession

    @session('otp')
        <div class="fixed top-4 right-4 z-[9999] w-[90%] max-w-sm sm:max-w-xs md:max-w-sm lg:max-w-md xl:max-w-lg"
            x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform" x-transition:leave-end="opacity-0 -translate-y-2">
            <div
                class="flex items-center justify-between p-4 text-sm font-semibold text-white bg-navy-blue rounded-xl shadow-lg">
                <span>{{ session('otp') }}</span>
                <button @click="show = false" class="ml-4 focus:outline-none">
                    @svg('eva-close', 'w-5 h-5 text-red-400 hover:text-red-500')
                </button>
            </div>
        </div>
    @endsession


    <h1 class="my-1 text-4xl font-extrabold capitalize">Welcome Back! </h1>

    <form wire:submit="login">


        <div>
            {{-- role selector button --}}
            <div class="w-full justify-evenly md:flex">
                <div class="w-full text-lg font-bold md:w-1/2 pr-7">
                    Login to access your Twellr account
                </div>
                <x-selector />

            </div>
            <div class="validator">
                <x-input-error :messages="$errors->get('role')" class="validator absolute" />
            </div>

            <!-- Session Status -->

            <!-- Email Address -->
            <div class="relative w-100">
                <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white mt-7" for="email"
                    :value="__('Email')" />

                <div class="absolute w-full mt-4">
                    <x-text-input wire:model="form.email" id="email" class="block w-full mt-5" type="email"
                        name="email" required autofocus autocomplete="email" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2 validator absolute" />
                </div>
            </div>

            <!-- Password -->
            <div class="relative mt-20 w-100">
                <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white mt-7" for="password"
                    :value="__('Password')" />

                <div class="absolute w-full mt-4">
                    <x-text-input wire:model="form.password" id="password" class="block w-full mt-5" type="password"
                        name="password" required autofocus autocomplete="password" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2 validator absolute" />
                </div>
            </div>

        </div>
        <div class="absolute z-50 mt-20 ">
            <input wire:model="form.remember" id="remember" type="checkbox"
                class="border-gray-300 rounded shadow-sm text-navy-blue focus:ring-navy-blue dark:focus:ring-navy-blue"
                name="remember">
            <x-input-label for="remember" :value="__('Remember Me')" class="inline-flex items-center mt-3 " />
        </div>
        <div class="relative block mt-40">
            <div>
                <x-form-action-button :value="__('Sign In')" />

                <p class="w-full my-2 text-center text-black">Or</p>

                <a href="{{ route('auth.google.login') }}" target="_top">
                    <x-google-auth-actions :value="__('Sign in with Google')" />
                </a>
            </div>
            <div class="flex mt-12">
                <a class="w-1/2 text-sm font-bold text-black underline rounded-md hover:text-navy-blue focus:outline-none focus:text-black"
                    href="{{ route('register') }}">
                    {{ __("Don't Have Account?") }}
                </a>
                @if (Route::has('password.request'))
                    <a class="w-1/2 text-sm font-bold text-right text-black underline rounded-md hover:text-navy-blue text-nowrap focus:outline-none focus:text-black"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
        </div>
    </form>


</div>
