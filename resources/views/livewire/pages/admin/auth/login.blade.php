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
    public string $role = 'admin';

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

        if (empty($emailVerified)) {
            $user = User::where('email', '=', $this->form->email)->first();

            $otp = Otp::generate($this->form->email);

            Mail::to($this->form->email)->send(new EmailVerification($otp, $user->name));

            return redirect(route('admin.email.verification'))->with(['user' => $user, 'secret' => $this->form->email]);
            ///
        } elseif (!empty($emailVerified)) {
            $this->form->authenticate();

            Session::regenerate(auth()->id());

            session()->flash('status', 'Login successful');

            $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
        }
    }
}; ?>

<div class="my-7 md:px-10">

    <x-auth-session-status class="mx-2" :status="session('status')" />

    <h1 class="my-1 mb-5 text-4xl font-extrabold capitalize">Welcome Back! </h1>

    <form wire:submit="login">


        <div>
            {{-- role selector button --}}
            <div class="w-full justify-evenly md:flex">
                <div class="w-full text-lg font-bold md:w-1/2 pr-7">
                    Login to access your Twellr account
                </div>
                <div class="flex gap-2 md:w-1/2 md:font-bold md">
                    <button
                        class="block w-full py-2.5 text-center text-lg bg-navy-blue text-white font-extrabold border rounded-lg cursor-default lg:px-3 xl:px-5 border-navy-blue">{{ __('Admin') }}

                    </button>
                </div>

            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-1" />
            <!-- Session Status -->

            <!-- Email Address -->
            <div class="relative w-100">
                <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white mt-7" for="email"
                    :value="__('Email')" />

                <div class="absolute w-full mt-4">
                    <x-text-input wire:model="form.email" id="email" class="block w-full mt-5" type="email"
                        name="email" required autofocus autocomplete="email" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>
            </div>

            <!-- Password -->
            <div class="relative mt-20 w-100">
                <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white mt-7" for="password"
                    :value="__('Password')" />

                <div class="absolute w-full mt-4">
                    <x-text-input wire:model="form.password" id="password" class="block w-full mt-5" type="password"
                        name="password" required autofocus autocomplete="password" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
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

                <a href="{{ route('auth.google.login') }}">
                    <x-google-auth-actions :value="__('Sign in with Google')" />
                </a>
            </div>
            @if (User::where('role', '=', 'admin')->get())
                <div class="flex mt-4">
                    <a class="w-1/2 text-sm font-bold text-black underline rounded-md hover:text-navy-blue focus:outline-none focus:text-black"
                        href="{{ route('admin.register') }}">
                        {{ __("Don't Have Account?") }}
                    </a>
                    @if (Route::has('password.request'))
                        <a class="w-1/2 text-sm font-bold text-right text-black underline rounded-md hover:text-navy-blue text-nowrap focus:outline-none focus:text-black"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>
            @endif

        </div>
    </form>


</div>
