<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>

rippen.com


    <form wire:submit="resetPassword" class="grid md:p-4 lg:px-10 ">
       <!-- Email Address -->
            <div class="relative w-100 ">
                <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white mt-7" for="email"
                    :value="__('Email')" />

                <div class="absolute w-full mt-4">
                    <x-text-input wire:model="email" id="email" class="block w-full mt-5" type="email"
                        name="email" required autofocus autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 validator absolute" />
                </div>
            </div>

            <!-- Password -->
            <div class="relative mt-20 w-100">
                <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white mt-7" for="password"
                    :value="__('Password')" />

                <div class="absolute w-full mt-4">
                    <x-text-input wire:model="password" id="password" class="block w-full mt-5" type="password"
                        name="password" required autofocus autocomplete="password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 validator absolute" />
                </div>
            </div>

         <!--  Confirm Password -->
                  <div class="relative mt-20 w-100">
                <x-input-label class="absolute z-50 px-1 ml-3 font-extrabold bg-white mt-7" for="password_ confirmation"
                    :value="__('Confirm Password')" />

                <div class="absolute w-full mt-4">
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full mt-5" type="password"
                        name="password_confirmation" required autofocus autocomplete="password_confirmation" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 validator absolute" />
                </div>
            </div>

        <div class="flex items-center justify-end mt-10">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</div>
