<?php

use App\Models\User;
use App\Helpers\FileHelper;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $address = '';
    public string $phone_no = '';
    public string $bank_name = '';
    public string $account_no = '';
    public string $account_name = '';
    public $avatar;
    public $avatarUpload;
    public $cover;
    public $coverUpload;
    public string $password = '';
    public string $current_password = '';
    public string $password_confirmation = '';
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
            ]);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>
<div class="bg-white h-screen w-screen" x-data="{ updated: false }" x-on:profile-updated="updated = true">
    <div class="w-full py-3 px-24 h-screen overflow-y-scroll mb-40 pb-20 ">
        <header class="">
            <h2 class="text-3xl font-extrabold text-gray-500">
                {{ __('User  Settings') }}
            </h2>
            <p class=" text-sm text-gray-500">
                Manage your account settings and preferences
            </p>
        </header>

        <div class="space-y-12 w-100 mt-2">
            <!--Profile Form content -->
            <div class="w-full " x-show="false">
                <div class="bg-white overflow-hidden rounded-lg shadow-neutral-700 shadow-md">
                    <div class="px-4 py-4 sm:px-6 bg-navy-blue">
                        <h3 class="text-lg font-medium leading-6 text-gray-100">Profile Information</h3>
                        <p class="mt-1 text-sm text-gray-100">
                            Update your account's profile information and email address.
                        </p>
                    </div>
                    <div class="border-t border-gray-400">
                        <form wire:submit.prevent="updateProfileInformation"
                            class="px-4 py-5 sm:p-6 space-y-6 bg-gray-100">
                            <div class="flex justify-between gap-10">
                                <div class="w-1/2 space-y-5">
                                    {{-- Name and Email --}}
                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <div class="sm:col-span-3">
                                            <label for="firstname" class="block text-sm font-medium text-gray-700">
                                                First name
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="firstname" id="firstname"
                                                    autocomplete="given-name" required
                                                    class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="lastname" class="block text-sm font-medium text-gray-700">
                                                Last name
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="lastname" id="lastname"
                                                    autocomplete="family-name" required
                                                    class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>

                                        <div class="sm:col-span-6">
                                            <label for="email" class="block text-sm font-medium text-gray-700">
                                                Email address
                                            </label>
                                            <div class="mt-1">
                                                <input type="email" wire:model="email" id="email"
                                                    autocomplete="email" required
                                                    class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>


                                    </div>

                                    {{-- Payment Preferences --}}
                                    @if (auth()->user()->role == 'creative')
                                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                            <div class="sm:col-span-3">
                                                <label for="address" class="block text-sm font-medium text-gray-700">
                                                    Address
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="address" id="address"
                                                        autocomplete="address" required
                                                        class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                            </div>

                                            <div class="sm:col-span-3">
                                                <label for="phone_no" class="block text-sm font-medium text-gray-700">
                                                    Phone No
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="phone_no" id="phone_no"
                                                        autocomplete="number" required
                                                        class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                            </div>

                                            <div class="sm:col-span-3">
                                                <label for="bank_name" class="block text-sm font-medium text-gray-700">
                                                    Bank
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="bank_name" id="bank_name"
                                                        autocomplete="bank" required
                                                        class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                            </div>

                                            <div class="sm:col-span-3">
                                                <label for="account_no" class="block text-sm font-medium text-gray-700">
                                                    Account No
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="account_no" id="account_no"
                                                        autocomplete="number" required
                                                        class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 @error('account_no') border-red-500 @enderror rounded-md">


                                                </div>
                                                <x-input-error for="account_no" :messages="$errors->get('account_no')" />
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label for="account_name"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Account Name
                                                </label>
                                                <div class="mt-1">
                                                    <input type="text" wire:model="account_name" id="account_name"
                                                        autocomplete="name" required
                                                        class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                            </div>


                                        </div>
                                    @endif

                                </div>

                                <div class="w-1/2">
                                    <div class="sm:col-span-6">
                                        <label for="avatar" class="block text-sm font-medium text-gray-700">
                                            Avatar
                                        </label>
                                        <div class="mt-1 flex items-center">

                                            <div class="flex-shrink-0">
                                                @if ($avatarUpload == null)
                                                    <img src="@if (empty(Auth::user()->avatar)) {{ asset('assets/icons-user.png') }}
                        @else
                        {{ asset('uploads/avatar/' . Auth::user()->avatar) }} @endif"
                                                        alt="avatar" class="h-12 w-12 rounded-full">
                                                @else
                                                    <img src="{{ $avatarUpload->temporaryUrl() }}" alt="avatar"
                                                        class="h-12 w-12 rounded-full @error('avatarUpload')
                                                            border-1 border-red-500
                                                        @enderror">
                                                @endif
                                            </div>
                                            <div class="ml-4 flex">
                                                <div
                                                    class="relative bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm flex items-center cursor-pointer hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-navy-blue">
                                                    <label for="avatar-upload"
                                                        class="relative text-sm font-medium text-navy-blue pointer-events-none">
                                                        <span>Change</span>
                                                        <span class="sr-only">avatar</span>
                                                    </label>
                                                    <input id="avatar-upload" wire:model='avatarUpload'
                                                        name="avatar" type="file" change="previewAvatar"
                                                        accept="image/*"
                                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer border-gray-300 rounded-md">
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">JPG, PNG, GIF up to 1MB</p>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="cover-image" class="block text-sm font-medium text-gray-700">
                                            Cover image
                                        </label>
                                        <label for="cover-image-upload">
                                            @if ($coverUpload == null)
                                                <div
                                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-500 border-dashed hover:border-navy-blue rounded-md">
                                                    <div class="space-y-1 text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-500"
                                                            stroke="currentColor" fill="none" viewBox="0 0 48 48"
                                                            aria-hidden="true">
                                                            <path
                                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <div class="flex text-sm text-gray-600  justify-center">
                                                            <label for="cover-image-upload"
                                                                class="relative cursor-pointer  rounded-md font-medium text-navy-blue hover:text-gray-500 focus-within:outline-none  text-center flex justify-center ">
                                                                <span>Upload a file</span>
                                                                <input id="cover-image-upload"
                                                                    wire:model='coverUpload' name="cover_image"
                                                                    type="file" accept="image/*" class="sr-only">
                                                            </label>

                                                        </div>
                                                        <p class="text-xs text-gray-500">
                                                            JPG, PNG, GIF up to 2MB
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-500 border-dashed hover:border-navy-blue rounded-md">
                                                    <div class="space-y-1 text-center">
                                                        <img src="{{ $coverUpload->temporaryUrl() }}" alt="cover">
                                                    </div>

                                                </div>
                                            @endif
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-blue hover:bg-navy-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-blue">
                                    Save
                                </button>
                                <div x-show="updated"
                                    class="ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-md">
                                    Profile updated successfully!
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!--Password Form content -->
            <div class="md:col-span-2 ">
                <div class="bg-white overflow-hidden rounded-lg shadow-neutral-700 shadow-md">
                    <div class="px-4 py-4 sm:px-6 bg-navy-blue">
                        <h3 class="text-lg font-medium leading-6 text-gray-100">Password Settings</h3>
                        <p class="mt-1 text-sm text-gray-100">
                            Update your Password.
                        </p>
                    </div>
                    <div class="border-t border-gray-400">
                        <form wire:submit.prevent="updatePassword" class="px-4 py-5 sm:p-6 space-y-6 bg-gray-200">
                            <div class="flex justify-between gap-10">
                                <div class="w-1/2">
                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <div class="sm:col-span-3">
                                            <label for="firstname" class="block text-sm font-medium text-gray-700">
                                                Current Password
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="current_password"
                                                    id="current_password" autocomplete="current_ password" required
                                                    class="shadow-sm focus:ing-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="lastname" class="block text-sm font-medium text-gray-700">
                                                Last name
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" wire:model="lastname" id="lastname"
                                                    autocomplete="family-name" required
                                                    class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>

                                        <div class="sm:col-span-6">
                                            <label for="email" class="block text-sm font-medium text-gray-700">
                                                Email address
                                            </label>
                                            <div class="mt-1">
                                                <input type="email" wire:model="email" id="email"
                                                    autocomplete="email" required
                                                    class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>


                                    </div>
                                </div>

                                <div class="w-1/2">
                                    <div class="sm:col-span-6">
                                        <label for="avatar" class="block text-sm font-medium text-gray-700">
                                            Avatar
                                        </label>
                                        <div class="mt-1 flex items-center">
                                            <div class="flex-shrink-0">
                                                <img src="@if (empty(Auth::user()->avatar)) {{ asset('assets/icons-user.png') }}
                        @else
                        {{ asset('uploads/avatar/' . Auth::user()->avatar) }} @endif"
                                                    alt="Current avatar" class="h-12 w-12 rounded-full">
                                            </div>
                                            <div class="ml-4 flex">
                                                <div
                                                    class="relative bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm flex items-center cursor-pointer hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-navy-blue">
                                                    <label for="avatar-upload"
                                                        class="relative text-sm font-medium text-navy-blue pointer-events-none">
                                                        <span>Change</span>
                                                        <span class="sr-only">avatar</span>
                                                    </label>
                                                    <input id="avatar-upload" name="avatar" type="file"
                                                        change="previewAvatar" accept="image/*"
                                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer border-gray-300 rounded-md">
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">JPG, PNG, GIF up to 1MB</p>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="cover-image" class="block text-sm font-medium text-gray-700">
                                            Cover image
                                        </label>
                                        <div
                                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor"
                                                    fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path
                                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600">
                                                    <label for="cover-image-upload"
                                                        class="relative cursor-pointer bg-white rounded-md font-medium text-navy-blue hover:text-navy-blue focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-navy-blue">
                                                        <span>Upload a file</span>
                                                        <input id="cover-image-upload" name="cover_image"
                                                            type="file" accept="image/*" class="sr-only">
                                                    </label>
                                                    <p class="pl-1">or drag and drop</p>
                                                </div>
                                                <p class="text-xs text-gray-500">
                                                    JPG, PNG, GIF up to 2MB
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-blue hover:bg-navy-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-blue">
                                    Save
                                </button>
                                @session('profile_updated')
                                    <div
                                        class="ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-md">
                                        Profile updated successfully!
                                    </div>
                                @endsession
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
    {{-- <script>
        let designFileInput = document.getElementById('design_stack')
        const designImage = document.getElementById('designImage');
        designFileInput.addEventListener('change', function() {
            const file = designFileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(e) {
                    designImage.src = e.target.result;
                }
            }
        })

        let printFileInput = document.getElementById('printable_stack')
        const printImage = document.getElementById('printImage');

        printFileInput.addEventListener('change', function() {
            const file = printFileInput.files[0];
            const extension = file.name.split('.').pop();
            console.log(extension);
            if (file && (extension == 'png' || extension == 'jpeg' || extension == 'jpg')) {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(e) {
                    printImage.src = e.target.result;
                    printImage.style.padding = "0px";
                }
            }
            if (extension != 'png' || extension != 'jpeg' || extension != 'jpg') {
                printImage.style.padding = "20px";
                printImage.src = "{{ asset('assets/file.png') }}";

            }
        })
    </script> --}}
</div>
