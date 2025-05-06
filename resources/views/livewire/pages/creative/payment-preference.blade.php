<?php

use App\Models\User;
use App\Models\State;
use App\Models\Country;
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
    public string $account_no = '';
    public $countries = [];
    public $states = [];
    public string $country;
    public string $state;

    public function mount()
    {
        $this->user = session()->get('user');
        if ($this->user) {
            $this->firstname = $this->user->firstname;
            $this->countries = Country::all();
        } else {
            abort(400, 'User not found in session');
        }
    }

    public function getStates($countryId)
    {
        return $this->states = State::where('country_id', '=', $countryId)->get();
    }

    public function storeInfo()
    {
        $validated = $this->validate([
            'firstname' => ['required', 'string', 'max:225'],
            'phone_no' => ['required', 'string', 'min:10'],
            'address' => ['required', 'string', 'max:225'],
            'bank_name' => ['required', 'string', 'max:225'],
            'account_name' => ['required', 'string', 'max:225'],
            'account_no' => ['required', 'string', 'min:10', 'max:10'],
            'country' => ['string', 'required'],
            'state' => ['string', 'required'],
        ],[
            'country.required' => 'Select a Country',
            'state.required' => 'Select a State',
            'country.string' => 'Select a Country',
            'state.string' => 'Select a State',
            'account_no.max' => 'Account Number must not exceed 10 digits',
            'account_no.min' => 'Account Number must be 10 digits',
             'phone_no.min' => 'Phone No must be 10 digits above',
        ]);
        dd($validated);
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
                'account_no' => $validated['account_no'],
                'country_id' => $validated['country'],
                'state_id' => $validated['country'],
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
<div class="flex justify-center px-24 py-10 mt-3 border border-black rounded-3xl md:mx-20">
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
                        <x-input-error :messages="$errors->get('phone_no')" class="absolute" />
                    </div>
                </div>
            </div>

            <div class="relative pr-2 mt-2 w-100">
                <x-input-label class="absolute z-50 px-1 mt-4 ml-3 font-extrabold bg-white" for="country"
                    :value="__('Country')" />

                <div class="absolute w-full mt-1">
                    <select wire:model.live="country" id="country"
                        class="block w-full mt-5 border-gray-500 text-black focus:border-navy-bluefocus:ring-navy-blue rounded-md shadow-sm"
                        type="text" name="country" required>
                        <option selected>Select a Country</option>
                        @forelse ($countries as $country)
                            <option :key="{{ $country->id }}" @click="$wire.getStates({{ $country->id }})"
                                value="{{ $country->id }}">{{ $country->name }}</option>
                        @empty
                            <option value="">. . . </option>
                        @endforelse
                    </select>

                    <x-input-error :messages="$errors->get('country')" class="mt-1 absolute" />
                </div>
            </div>

            <div class="flex w-full justify-evenly mt-20 pt-1">
                <div class="w-1/2  pr-2">
                    <x-input-label class="absolute z-50 px-1 mt-3 ml-3 font-extrabold bg-white" for="state"
                        :value="__('State')" />

                    <div class="relative mt-4">
                        <select wire:model.live="state" id="state"
                            class="block w-full mt-5 border-gray-500 text-black focus:border-navy-blue
    focus:ring-navy-blue rounded-md shadow-sm"
                            name="state" required>
                            <option selected>Select a State</option>
                            @forelse ($states as $state)
                                <option :key="{{ $state->id }}" value="{{ $state->id }}">{{ $state->name }}
                                </option>
                            @empty
                                <option value=""> . . </option>
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('state')" class="mt-1 absolute" />
                    </div>
                </div>

                <div class="w-1/2 ml-2 ">
                    <x-input-label class="absolute z-50 px-1 mt-3 ml-3 font-extrabold bg-white" for="address"
                        :value="__('Address')" />

                    <div class="relative mt-4">
                        <x-text-input wire:model="address" id="address" class="block w-full mt-5 border-5"
                            type="text" name="address" required />
                        <x-input-error :messages="$errors->get('address')" class="z-50 mt-2" />
                    </div>
                </div>
            </div>



        </div>
        <div class="relative flex justify-center w-full  text-xl font-bold text-center">
            <p class="absolute my-8">Set Payment Preference</p>
        </div>

        <div class="relative my-20">
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
                    <x-text-input wire:model="account_no" id="account_number" class="block w-full mt-5" type="text"
                        name="account_number" required autofocus autocomplete="account_number" />
                    <x-input-error :messages="$errors->get('account_no')" class="mt-1 text-center" />
                </div>
            </div>

        </div>

        <x-primary-button class="w-full mt-5">
            {{ __('Continue') }}
        </x-primary-button>

    </form>



</div>
