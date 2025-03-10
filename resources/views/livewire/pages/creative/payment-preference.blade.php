<?php

use App\Models\User;
use Tzsk\Otp\Facades\Otp;
use Livewire\Volt\Component;
use App\Mail\EmailVerification;
use Livewire\Attributes\Layout;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\Authenticatable;

new #[Layout('layouts.guest')] class extends Component {
    public $user = '';
    public $firstname = '';
    public string $phone_no = '';
    public string $address = '';
    public string $bank_name = '';
    public string $account_name = '';
    public string $account_number = '';

    public function mount()
    {
        $this->user = session('user');
        if ($this->user) {
            $this->firstname = $this->user->firstname;
        } else {
            abort(500, 'User not found in session');
        }
    }

    public function storeInfo()
    {
        $validated = $this->validate([
            'firstname' => ['required', 'string', 'max:225'],
            'phone_no' => ['required', 'string', 'max:11'],
            'address' => ['required', 'string', 'max:225'],
            'bank_name' => ['required', 'string', 'max:225'],
            'account_name' => ['required', 'string', 'max:225'],
            'account_number' => ['required', 'string', 'min:10', 'max:10'],
        ]);

        $newUser = $this->user->id;

        if ($newUser) {
            $user = User::where('id', '=', $newUser)->first();

            if (!$user) {
                abort(500);
            }

            $user->update([
                'firstname' => $validated['firstname'],
                'phone_no' => $validated['phone_no'],
                'address' => $validated['address'],
                'bank_name' => $validated['bank_name'],
                'account_name' => $validated['account_name'],
                'account_number' => $validated['account_number'],
            ]);

            //$user->save();

            Auth::login($this->user);

            Session::regenerate(auth()->id());

            Session::flash('status', 'Success');

            return redirect(route('dashboard', absolute: false));
        } else {
            Session::flash('status', 'Error');
        }
    }
};
?>
<div class="justify-center flex px-24 py-10 mt-3 border border-black rounded-3xl ">
    <form class="w-[50%]" wire:submit.prevent="storeInfo">
        <div class="relative ">

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

                <div class="w-1/2 ml-2 ">
                    <x-input-label class="absolute z-50 px-1 mt-3 ml-3 font-extrabold bg-white" for="phone_no"
                        :value="__('Phone No')" />

                    <div class="relative mt-4">
                        <x-text-input wire:model="phone_no" id="phone_no" class="block w-full mt-5" type="tel"
                            name="phone_no" required autofocus autocomplete="phone_no" />
                        <x-input-error :messages="$errors->get('phone_no')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="relative pr-2 mt-2 w-100">
                <x-input-label class="absolute z-50 px-1 mt-4 ml-3 font-extrabold bg-white" for="address"
                    :value="__('Address')" />

                <div class="absolute w-full mt-1">
                    <x-text-input wire:model="address" id="address" class="block w-full mt-5" type="text"
                        name="address" required autofocus autocomplete="address" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>
            </div>
        </div>
        <div class="relative my-20 w-full text-center flex justify-center text-xl font-bold">
            <p class="absolute my-10">Set Payment Preference</p>
        </div>

        <div class="relative my-24">
            <div class="flex w-full justify-evenly">
                <!-- First Name -->
                <div class="w-1/2 pr-2 ">
                    <x-input-label class="absolute z-50 px-1 mt-3 ml-3 font-extrabold bg-white" for="bank_name"
                        :value="__('Bank Name')" />

                    <div class="relative mt-4">
                        <x-text-input wire:model="bank_name" id="bank_name" class="block w-full mt-5 border-5"
                            type="text" name="bank_name" required autofocus autocomplete="bank_name" />
                        <x-input-error :messages="$errors->get('bank_name')" class="z-50 mt-2" />
                    </div>
                </div>

                <!-- Last Name -->
                <div class="w-1/2 ml-2 ">
                    <x-input-label class="absolute z-50 px-1 mt-3 ml-3 font-extrabold bg-white" for="account_name"
                        :value="__('Account Name')" />

                    <div class="relative mt-4">
                        <x-text-input wire:model="account_name" id="account_name" class="block w-full mt-5"
                            type="text" name="account_name" required autofocus autocomplete="account_name" />
                        <x-input-error :messages="$errors->get('account_name')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Email Address -->
            <div class="relative pr-2 mt-2 w-100">
                <x-input-label class="absolute z-50 px-1 mt-4 ml-3 font-extrabold bg-white" for="email"
                    :value="__('Account Number')" />

                <div class="absolute w-full mt-1">
                    <x-text-input wire:model="account_number" id="account_number" class="block w-full mt-5"
                        type="text" name="account_number" required autofocus autocomplete="account_number" />
                    <x-input-error :messages="$errors->get('account_number')" class="mt-2 text-center" />
                </div>
            </div>

        </div>

        <x-primary-button class="w-full mt-2">
            {{ __('Continue') }}
        </x-primary-button>

    </form>



</div>
