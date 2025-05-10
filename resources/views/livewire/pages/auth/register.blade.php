<?php

use App\Models\User;
use App\Models\Referral;
use Tzsk\Otp\Facades\Otp;
use Illuminate\Http\Request;
use Livewire\Volt\Component;
use App\Mail\EmailVerification;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

new #[Layout('layouts.guest')] class extends Component {
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'user';
    public ?string $referral;

    public function mount(Request $request)
    {
        $this->referral = $request->query('referral');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request)
    {
        $validated = $this->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'referral' => ['nullable', 'string', 'exists:' . User::class . ',referral_link'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $referrer = $validated['referral'] ? User::where('referral_link', $validated['referral'])->first() : '';

        event(
            new Registered(
                ($user = User::create([
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role' => $validated['role'],
                    'referral_link' => strtoupper(substr($validated['firstname'], 0, 2) . substr($validated['lastname'], 0, 2) . rand(1000, 9999)),
                    'notify_purchase' => $validated['role'] == 'creative' ? 'yes' : 'no',
                    'referred_by' => $referrer ? $referrer->id : '',
                ])),
            ),
        );

        if ($referrer) {
            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'code_used' => $validated['referral'],
                'status' => 'pending',
            ]);
        } else {
            abort('500', 'Something went wrong but we are working on it');
        }

        $otp = Otp::generate($validated['email']);

        Mail::to($this->email)->send(new EmailVerification($otp, $user->name));

        session()->flash('message', 'Registration successful');
        session()->reflash();
        session()->put('user', $user);

        redirect(route('email.verification'))->with(['user' => $user, 'secret' => $validated['email']]);
    }
}; ?>

<div class="h-screen lg:px-10 my-7 border-0 md:my-10">
    <h1 class="w-full my-1 md:mb-5 text-3xl font-bold  md:text-4xl text-left ">Create Account</h1>
    <div>

   @session('message')
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
                <span>Registration successful.</span>
                <button @click="show = false" class="ml-4 focus:outline-none">
                    @svg('eva-close', 'w-5 h-5 text-red-400 hover:text-red-500')
                </button>
            </div>
        </div>
    @endsession
@endsession

    </div>
    <form wire:submit.prevent="register">
        {{-- role selector button --}}
        <input type="text" wire:model="referral" class="hidden" />
        <div class="">
            <div class="md:flex justify-evenly">
                <div
                    class="mb-3 font-semibold md:w-1/2 md:py-3 lg:py-0 text-left lg:pr-10 md:text-lg md:font-bold">
                    Sign up to create an account on Twellr
                </div>
                <x-selector />

                <x-input-error :messages="$errors->get('role')" class="z-50 mt-2" />



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
                    <x-text-input wire:model="email" id="email" class="block w-full mt-5" type="email"
                        name="email" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>

            <!-- Password -->
            <div class="relative pt-1 pr-2 mt-24 w-100">
                <x-input-label class="absolute z-50 px-1 mt-1 ml-3 font-extrabold bg-white md:mt-2" for="password"
                    :value="__('Password')" />

                <div class="absolute w-full md:mt-1 ">
                    <x-text-input wire:model="password" id="password" class="block w-full mt-3 " type="password"
                        name="password" required autocomplete="password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="relative block mt-3 md:mt-16">
            <div>
                <x-form-action-button :value="__('Create Account')" />


            </div>
            <div class="flex items-center justify-end mt-1 md:mt-10">
                <a class="text-sm font-bold text-black underline rounded-md hover:text-navy-blue dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 "
                    href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>


            </div>
        </div>
    </form>
</div>
