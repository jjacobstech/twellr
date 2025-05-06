<?php
use App\Models\User;
use App\Models\State;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Helpers\FileHelper;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

new class extends Component {
    use WithFileUploads;
    use Toast;

    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public ?string $address = '';
    public ?string $phone_no = '';
    public ?string $bank_name = '';
    public ?string $account_no = '';
    public ?string $account_name = '';
    public $avatar;
    public $avatarUpload;
    public $cover;
    public $coverUpload;
    public ?string $facebook;
    public ?string $whatsapp;
    public ?string $x;
    public ?string $instagram;
    public $states;
    public $state;
    public $countries;
    public $country;
    public $cropConfig = [
        'width' => 50,
        'height' => 50,
        'aspectRatio' => 1,
        'minWidth' => 50,
        'minHeight' => 50,
        'maxWidth ' => 500,
        'maxHeight' => 500,
        'cropWidth' => 50,
        'cropHeight' => 50,
        'cropAspectRatio' => 1,
        'cropMinWidth' => 50,
        'cropMinHeight' => 50,
        'cropMaxWidth' => 500,
        'cropMaxHeight' => 500,
        'cropPreview' => true,
        'cropPreviewWidth' => 50,
        'cropPreviewHeight' => 50,
        'cropPreviewAspectRatio' => 1,
        'cropPreviewMinWidth' => 50,
        'cropPreviewMinHeight' => 50,
        'cropPreviewMaxWidth' => 500,
        'cropPreviewMaxHeight' => 500,
        'cropPreviewBackground' => '#fff',
        'cropPreviewBorder' => '1px solid #000',
        'cropPreviewBorderRadius' => '50%',
        'cropPreviewBoxShadow' => '0 0 5px rgba(0, 0, 0, 0.5)',
        'cropPreviewOpacity' => 1,
        'cropPreviewZIndex' => 1,
        'cropPreviewPosition' => 'absolute',
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->firstname = Auth::user()->firstname;
        $this->lastname = Auth::user()->lastname;
        $this->email = Auth::user()->email;
        $this->cover = Auth::user()->cover;
        $this->avatar = Auth::user()->avatar;
        $this->address = Auth::user()->address;
        $this->phone_no = Auth::user()->phone_no;
        $this->bank_name = Auth::user()->bank_name;
        $this->account_no = Auth::user()->account_no;
        $this->account_name = Auth::user()->account_name;
        $this->x = Auth::user()->x;
        $this->whatsapp = Auth::user()->whatsapp;
        $this->facebook = Auth::user()->facebook;
        $this->instagram = Auth::user()->instagram;
        $this->withdrawalThreshold = Auth::user()->withdrawal_threshold;
        $this->states = State::all();
        $this->countries = Country::all();
        $this->state = Auth::user()->state_id ?? '';
        $this->country = Auth::user()->country_id ?? "";
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $userInfo = (object) ($validated = $this->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'avatarUpload' => $this->avatarUpload == null ? '' : (empty(Auth::user()->avatar) ? ['required', 'image', 'mimes:jpg,jpeg,png'] : ''),
            'coverUpload' => $this->coverUpload == null ? '' : (empty(Auth::user()->cover) ? ['required', 'image', 'mimes:jpg,jpeg,png'] : ''),
            'address' => ['required', 'string', 'max:255'],
            'phone_no' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_no' => ['required', 'string', 'max:255', 'min:10', 'max:10'],
            'account_name' => ['required', 'string', 'max:255'],
            'x' => ['string', 'max:255'],
            'facebook' => ['string', 'max:255'],
            'whatsapp' => ['string', 'max:255'],
            'instagram' => ['string', 'max:255'],
            'state' => ['numeric'],
            'country' => ['numeric'],
        ]));

        $avatar = $userInfo->avatarUpload;
        $cover = $userInfo->coverUpload;

        $coverExist = $cover ? Storage::disk()->exists('cover/' . Auth::user()->cover) : '';
        $avatarExist = $avatar ? Storage::disk()->exists('avatar/' . Auth::user()->avatar) : '';

        $coverDeleted = $coverExist ? Storage::delete('cover/' . Auth::user()->cover) : '';
        $avatarDeleted = $avatarExist ? Storage::delete('avatar/' . Auth::user()->avatar) : '';

        //File Data Fetch
        $avatarData = $avatar ? FileHelper::getFileData($avatar) : '';
        $coverData = $cover ? FileHelper::getFileData($cover) : '';

        // Save Files Into Storage
        $avatarSaved = $avatar ? FileHelper::saveFile($avatar, 'avatar/' . $avatarData->name) : '';
        $coverSaved = $cover ? FileHelper::saveFile($cover, 'cover/' . $coverData->name) : '';

        if ($this->avatarUpload == null && $this->coverUpload == null) {
            $user->fill([
                'firstname' => $userInfo->firstname,
                'lastname' => $userInfo->lastname,
                'email' => $userInfo->email,
                'address' => $userInfo->address,
                'phone_no' => $userInfo->phone_no,
                'bank_name' => $userInfo->bank_name,
                'account_no' => $userInfo->account_no,
                'account_name' => $userInfo->account_name,
                'x' => $userInfo->x,
                'facebook' => $userInfo->facebook,
                'whatsapp' => $userInfo->whatsapp,
                'instagram' => $userInfo->instagram,
                'country_id' => $userInfo->country,
                'state_id' => $userInfo->state,
            ]);
        } elseif ($this->avatarUpload == null) {
            $user->fill([
                'firstname' => $userInfo->firstname,
                'lastname' => $userInfo->lastname,
                'email' => $userInfo->email,
                'cover' => $coverData->name,
                'address' => $userInfo->address,
                'phone_no' => $userInfo->phone_no,
                'bank_name' => $userInfo->bank_name,
                'account_no' => $userInfo->account_no,
                'account_name' => $userInfo->account_name,
                'x' => $userInfo->x,
                'facebook' => $userInfo->facebook,
                'whatsapp' => $userInfo->whatsapp,
                'instagram' => $userInfo->instagram,
                   'country_id' => $userInfo->country,
                'state_id' => $userInfo->state,
            ]);
        } elseif ($this->coverUpload == null) {
            $user->fill([
                'firstname' => $userInfo->firstname,
                'lastname' => $userInfo->lastname,
                'email' => $userInfo->email,
                'avatar' => $avatarData->name,
                'address' => $userInfo->address,
                'phone_no' => $userInfo->phone_no,
                'bank_name' => $userInfo->bank_name,
                'account_no' => $userInfo->account_no,
                'account_name' => $userInfo->account_name,
                'x' => $userInfo->x,
                'facebook' => $userInfo->facebook,
                'whatsapp' => $userInfo->whatsapp,
                'instagram' => $userInfo->instagram,
                   'country_id' => $userInfo->country,
                'state_id' => $userInfo->state,
            ]);
        } else {
            $user->fill([
                'firstname' => $userInfo->firstname,
                'lastname' => $userInfo->lastname,
                'email' => $userInfo->email,
                'avatar' => $avatarData->name,
                'cover' => $coverData->name,
                'address' => $userInfo->address,
                'phone_no' => $userInfo->phone_no,
                'bank_name' => $userInfo->bank_name,
                'account_no' => $userInfo->account_no,
                'account_name' => $userInfo->account_name,
                'x' => $userInfo->x,
                'facebook' => $userInfo->facebook,
                'whatsapp' => $userInfo->whatsapp,
                'instagram' => $userInfo->instagram,
                   'country_id' => $userInfo->country,
                'state_id' => $userInfo->state,
            ]);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        if (session()->has('was_redirected')) {
            $this->redirectIntended(route('market.place'), true);
        } else {
            $this->dispatch('profile-updated', name: $user->name);
        }
        $this->mount();
        // $this->redirectIntended(route('settings'), true);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<!--Profile Form content -->
<div class="w-full" x-data="{ remove: true }" x-on:livewire-upload-error="$wire.error('Upload Failed')"
    x-on:livewire-upload-finish="$wire.success('Upload Successful')">
    <div class="overflow-hidden bg-white rounded-lg shadow-md shadow-neutral-700 scrollbar-none">
        <div class="px-4 py-4 sm:px-6 bg-navy-blue">
            <h3 class="text-lg font-medium leading-6 text-gray-100">Profile Information</h3>
            <p class="mt-1 text-sm text-gray-100">
                Update your account's profile information and email address.
            </p>
        </div>
        <div class="border-t border-gray-400 ">
            <form wire:submit="updateProfileInformation" class="px-4 py-5 space-y-6 bg-gray-100 sm:p-6 ">
                <div class="grid justify-between md:flex md:gap-10">
                    <div class="space-y-5 md:w-1/2">
                        {{-- Name and Email --}}
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="col-span-12 md:col-span-3">
                                <label for="firstname" class="block text-sm font-medium text-gray-700">
                                    First name
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="firstname" id="firstname"
                                        autocomplete="given-name" required
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('firstname') border-red-500 @enderror">
                                </div>
                            </div>

                            <div class="col-span-12 md:col-span-3">
                                <label for="lastname" class="block text-sm font-medium text-gray-700">
                                    Last name
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="lastname" id="lastname"
                                        autocomplete="family-name" required
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('lastname') border-red-500 @enderror">
                                </div>
                            </div>

                            <div class="col-span-12 md:col-span-3">
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    Email address
                                </label>
                                <div class="mt-1">
                                    <input type="email" wire:model.live="email" id="email" autocomplete="email"
                                        required
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('email') border-red-500 @enderror">
                                </div>
                            </div>



                            <div class="col-span-12 md:col-span-3">
                                <label for="address" class="block text-sm font-medium text-gray-700">
                                    Address
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="address" id="address"
                                        autocomplete="address" required
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('address') border-red-500 @enderror">
                                </div>
                            </div>

                            <div class="col-span-12 md:col-span-3">
                                <label for="state" class="block text-sm font-medium text-gray-700">
                                    State
                                </label>
                                <div class="mt-1">

                                    <select wire:model.live="state" id="state"
                                         class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('address') border-red-500 @enderror">
                                        <option value="">Select State</option>
                                        @forelse ($states as $state)
                                            <option
                                                value="{{ $state->id }}">{{ $state->name }}</option>
                                        @empty
                                            <option value="">. . .</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                             <div class="col-span-12 md:col-span-3">
                                <label for="country" class="block text-sm font-medium text-gray-700">
                                    Country
                                </label>
                                <div class="mt-1">

                                    <select wire:model.live="country" id="country"
                                         class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('address') border-red-500 @enderror">
                                        <option value="">Select Country</option>
                                        @forelse ($countries as $country)
                                            <option
                                                value="{{ $country->id }}">{{ $country->name }}</option>
                                        @empty
                                            <option value="">. . .</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>


                            <div class="col-span-12 md:col-span-3">
                                <label for="phone_no" class="block text-sm font-medium text-gray-700">
                                    Phone No
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="phone_no" id="phone_no"
                                        autocomplete="number" required
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('phone_no') border-red-500 @enderror">
                                </div>
                            </div>

                            <div class="col-span-12 md:col-span-3">
                                <label for="bank_name" class="block text-sm font-medium text-gray-700">
                                    Bank
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="bank_name" id="bank_name" autocomplete="bank"
                                        required
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('bank_name') border-red-500 @enderror">
                                </div>
                            </div>

                            <div class="col-span-12 md:col-span-3">
                                <label for="account_no" class="block text-sm font-medium text-gray-700">
                                    Account No
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="account_no" id="account_no"
                                        autocomplete="number" required
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 @error('account_no') border-red-500 @enderror rounded-md">


                                </div>
                                <x-input-error for="account_no" :messages="$errors->get('account_no')" />
                            </div>

                            <div class="col-span-12 md:col-span-3">
                                <label for="account_name" class="block text-sm font-medium text-gray-700">
                                    Account Name
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model.live="account_name" id="account_name"
                                        autocomplete="name" required
                                        class="block w-full border-gray-300 rounded-md shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue sm:text-sm">
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-3">
                                <label for="facebook" class="block text-sm font-medium text-gray-700">
                                    <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                        <path
                                            d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64h98.2V334.2H109.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H255V480H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z" />
                                    </svg>
                                </label>
                                <div class="mt-1">

                                    <input type="text" wire:model.live="facebook" id="facebook"
                                        autocomplete="social-media"
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('facebook')
                                            border-red-500
                                        @enderror">
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-3 ">
                                <label for="x" class="block text-sm font-medium text-gray-700"> <svg
                                        class="w-10 h-10" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                        <path
                                            d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm297.1 84L257.3 234.6 379.4 396H283.8L209 298.1 123.3 396H75.8l111-126.9L69.7 116h98l67.7 89.5L313.6 116h47.5zM323.3 367.6L153.4 142.9H125.1L296.9 367.6h26.3z" />
                                    </svg></label>
                                <div class="mt-1">

                                    <input type="text" wire:model.live="x" id="x"
                                        autocomplete="social-media"
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('x')
                                            border-red-500
                                        @enderror">
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-3">
                                <label for="instagram" class="block text-sm font-medium text-gray-700"><svg
                                        class="w-10 h-10" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                        <path
                                            d="M194.4 211.7a53.3 53.3 0 1 0 59.3 88.7 53.3 53.3 0 1 0 -59.3-88.7zm142.3-68.4c-5.2-5.2-11.5-9.3-18.4-12c-18.1-7.1-57.6-6.8-83.1-6.5c-4.1 0-7.9 .1-11.2 .1c-3.3 0-7.2 0-11.4-.1c-25.5-.3-64.8-.7-82.9 6.5c-6.9 2.7-13.1 6.8-18.4 12s-9.3 11.5-12 18.4c-7.1 18.1-6.7 57.7-6.5 83.2c0 4.1 .1 7.9 .1 11.1s0 7-.1 11.1c-.2 25.5-.6 65.1 6.5 83.2c2.7 6.9 6.8 13.1 12 18.4s11.5 9.3 18.4 12c18.1 7.1 57.6 6.8 83.1 6.5c4.1 0 7.9-.1 11.2-.1c3.3 0 7.2 0 11.4 .1c25.5 .3 64.8 .7 82.9-6.5c6.9-2.7 13.1-6.8 18.4-12s9.3-11.5 12-18.4c7.2-18 6.8-57.4 6.5-83c0-4.2-.1-8.1-.1-11.4s0-7.1 .1-11.4c.3-25.5 .7-64.9-6.5-83l0 0c-2.7-6.9-6.8-13.1-12-18.4zm-67.1 44.5A82 82 0 1 1 178.4 324.2a82 82 0 1 1 91.1-136.4zm29.2-1.3c-3.1-2.1-5.6-5.1-7.1-8.6s-1.8-7.3-1.1-11.1s2.6-7.1 5.2-9.8s6.1-4.5 9.8-5.2s7.6-.4 11.1 1.1s6.5 3.9 8.6 7s3.2 6.8 3.2 10.6c0 2.5-.5 5-1.4 7.3s-2.4 4.4-4.1 6.2s-3.9 3.2-6.2 4.2s-4.8 1.5-7.3 1.5l0 0c-3.8 0-7.5-1.1-10.6-3.2zM448 96c0-35.3-28.7-64-64-64H64C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96zM357 389c-18.7 18.7-41.4 24.6-67 25.9c-26.4 1.5-105.6 1.5-132 0c-25.6-1.3-48.3-7.2-67-25.9s-24.6-41.4-25.8-67c-1.5-26.4-1.5-105.6 0-132c1.3-25.6 7.1-48.3 25.8-67s41.5-24.6 67-25.8c26.4-1.5 105.6-1.5 132 0c25.6 1.3 48.3 7.1 67 25.8s24.6 41.4 25.8 67c1.5 26.3 1.5 105.4 0 131.9c-1.3 25.6-7.1 48.3-25.8 67z" />
                                    </svg>
                                </label>
                                <div class="mt-1">

                                    <input type="text" wire:model.live="instagram" id="instagram"
                                        autocomplete="social-media"
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('instagram')
                                            border-red-500
                                        @enderror">
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-3">
                                <label for="whatsapp" class="block text-sm font-medium text-gray-700"><svg
                                        class="w-10 h-10" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                        <path
                                            d="M92.1 254.6c0 24.9 7 49.2 20.2 70.1l3.1 5-13.3 48.6L152 365.2l4.8 2.9c20.2 12 43.4 18.4 67.1 18.4h.1c72.6 0 133.3-59.1 133.3-131.8c0-35.2-15.2-68.3-40.1-93.2c-25-25-58-38.7-93.2-38.7c-72.7 0-131.8 59.1-131.9 131.8zM274.8 330c-12.6 1.9-22.4 .9-47.5-9.9c-36.8-15.9-61.8-51.5-66.9-58.7c-.4-.6-.7-.9-.8-1.1c-2-2.6-16.2-21.5-16.2-41c0-18.4 9-27.9 13.2-32.3c.3-.3 .5-.5 .7-.8c3.6-4 7.9-5 10.6-5c2.6 0 5.3 0 7.6 .1c.3 0 .5 0 .8 0c2.3 0 5.2 0 8.1 6.8c1.2 2.9 3 7.3 4.9 11.8c3.3 8 6.7 16.3 7.3 17.6c1 2 1.7 4.3 .3 6.9c-3.4 6.8-6.9 10.4-9.3 13c-3.1 3.2-4.5 4.7-2.3 8.6c15.3 26.3 30.6 35.4 53.9 47.1c4 2 6.3 1.7 8.6-1c2.3-2.6 9.9-11.6 12.5-15.5c2.6-4 5.3-3.3 8.9-2s23.1 10.9 27.1 12.9c.8 .4 1.5 .7 2.1 1c2.8 1.4 4.7 2.3 5.5 3.6c.9 1.9 .9 9.9-2.4 19.1c-3.3 9.3-19.1 17.7-26.7 18.8zM448 96c0-35.3-28.7-64-64-64H64C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96zM148.1 393.9L64 416l22.5-82.2c-13.9-24-21.2-51.3-21.2-79.3C65.4 167.1 136.5 96 223.9 96c42.4 0 82.2 16.5 112.2 46.5c29.9 30 47.9 69.8 47.9 112.2c0 87.4-72.7 158.5-160.1 158.5c-26.6 0-52.7-6.7-75.8-19.3z" />
                                    </svg>
                                </label>
                                <div class="mt-1 ">

                                    <input type="text" placeholder="Paste whatsapp link here"
                                        wire:model.live="whatsapp" id="whatsapp" autocomplete="social-media"
                                        class="shadow-sm text-gray-700 focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('whatsapp')
                                            border-red-500
                                        @enderror">
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="grid gap-2 mt-5 md:w-1/2 md:mt-0">
                        <div class="space-y-2">
                            <p class="font-extrabold text-gray-700 text-md">
                                Avatar
                            </p>
                            <x-mary-file wire:model.live="avatarUpload" accept="image/png, image/jpeg, image/jpg">
                                <img class="w-20 h-20 rounded-full"
                                    src="{{ Auth::user()->avatar ? asset('uploads/avatar/' . Auth::user()->avatar) : asset('assets/icons-user.png') }}"
                                    alt="cover">
                            </x-mary-file>
                            <p class="text-xs text-gray-700">JPG, PNG, GIF</p>
                        </div>

                        <div class="md:col-span-6">
                            <p class="block font-extrabold text-gray-700 text-md">
                                Cover image
                            </p>
                            <label
                                @if (Auth::user()->cover == null) :class="remove ? ' border-2 border-gray-500 border-dashed rounded-md hover:border-navy-blue' :
                                    'border-0'" @endif
                                class="flex justify-center px-3 py-3 mt-1 ">
                                @if (Auth::user()->cover == null)
                                    <div x-show="remove" class="space-y-1 text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-500" stroke="currentColor"
                                            fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex justify-center text-sm text-gray-600">
                                            <label for="cover-image-upload"
                                                class="relative flex justify-center font-medium text-center text-gray-500 rounded-md cursor-pointer hover:text-neutral-900 focus-within:outline-none ">
                                                <span>Upload a file</span>
                                            </label>

                                        </div>
                                        <p class="text-xs text-gray-500">
                                            JPG, PNG, GIF up to 2MB
                                        </p>
                                    </div>
                                @endif
                                <x-mary-file @click="remove = !remove" wire:model.live="coverUpload"
                                    accept="image/png, image/jpeg, image/jpg">
                                    <img :class="remove ? '' :
                                        'border-2 border-gray-500 border-dashed rounded-md hover:border-navy-blue p-2'"
                                        src="{{ Auth::user()->cover ? asset('uploads/cover/' . Auth::user()->cover) : '' }}"
                                        class="@if (Auth::user()->cover) border-2 border-gray-500 border-dashed rounded-md hover:border-navy-blue p-2 @endif">
                                </x-mary-file>


                            </label>
                        </div>
                    </div>

                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-navy-blue hover:bg-navy-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-blue">
                        Save
                    </button>
                    <div x-show="updated"
                        class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-green-700 bg-green-100 rounded-md">
                        Profile updated successfully!
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
