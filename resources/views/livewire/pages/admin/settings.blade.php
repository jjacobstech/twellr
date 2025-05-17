<?php
use App\Models\User;
use Mary\Traits\Toast;
use App\Helpers\FileHelper;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Livewire\Actions\Logout;
use function Livewire\Volt\{state, usesFileUploads, uses, layout};

layout('layouts.admin');

usesFileUploads();
uses(Toast::class);

state([
    'firstname' => fn() => Auth::user()->firstname,
    'lastname' => fn() => Auth::user()->lastname,
    'email' => fn() => Auth::user()->email,
    'avatar' => fn() => Auth::user()->cover ?? null,
    'avatarUpload' => null,
    'cover' => fn() => Auth::user()->cover ?? null,
    'coverUpload' => null,
    'current_password' => '',
    'password' => '',
    'password_confirmation' => '',
]);

/**
 * Update the profile information for the currently authenticated user.
 */
$updateProfileInformation = function (): void {
    $user = Auth::user();

    $userInfo = (object) ($validated = $this->validate([
        'firstname' => ['required', 'string', 'max:255'],
        'lastname' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        'avatarUpload' => $this->avatarUpload == null ? '' : (empty(Auth::user()->avatar) ? ['required', 'image', 'mimes:jpg,jpeg,png'] : ''),
        'coverUpload' => $this->coverUpload == null ? '' : (empty(Auth::user()->cover) ? ['required', 'image', 'mimes:jpg,jpeg,png'] : ''),
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
        ]);
    } elseif ($this->avatarUpload == null) {
        $user->fill([
            'firstname' => $userInfo->firstname,
            'lastname' => $userInfo->lastname,
            'email' => $userInfo->email,
            'cover' => $coverData->name,
        ]);
    } elseif ($this->coverUpload == null) {
        $user->fill([
            'firstname' => $userInfo->firstname,
            'lastname' => $userInfo->lastname,
            'email' => $userInfo->email,
            'avatar' => $avatarData->name,
        ]);
    } else {
        $user->fill([
            'firstname' => $userInfo->firstname,
            'lastname' => $userInfo->lastname,
            'email' => $userInfo->email,
            'avatar' => $avatarData->name,
            'cover' => $coverData->name,
        ]);
    }

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();
    if (session()->has('was_redirected')) {
        $this->redirectIntended(route('market.place'), true);
    } else {
        $this->success('Profile Updated');
    }
    $this->redirectIntended(route('admin.settings'), true);
};

$updatePassword = function (): void {
    try {
        $validated = $this->validate(
            [
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
                'password_confirmation' => ['required', 'string'],
            ],
            ['current_password.required' => 'Current Password cannot be empty', 'password.required' => 'Password cannot be empty', 'password_confirmation.required' => 'Confirm Password cannot be empty'],
        );
    } catch (ValidationException $e) {
        $this->reset('current_password', 'password', 'password_confirmation');

        throw $e;
    }

    Auth::user()->update([
        'password' => Hash::make($validated['password']),
    ]);

    $this->reset('current_password', 'password', 'password_confirmation');

    $this->dispatch('password-updated');
};

$deleteUser = function (Logout $logout): void {
    $this->validate([
        'password' => ['required', 'string', 'current_password'],
    ]);

    tap(Auth::user(), $logout(...))->delete();

    $this->redirect('/', navigate: true);
};
/**
 * Send an email verification notification to the current user.
 */
$sendVerification = function (): void {
    $user = Auth::user();

    if ($user->hasVerifiedEmail()) {
        $this->redirectIntended(default: route('dashboard', absolute: false));

        return;
    }

    $user->sendEmailVerificationNotification();

    $this->success('status', 'verification-link-sent');
};

?>
<div class="fixed grid w-full h-screen gap-1 pt-1 pb-40 bg-gray-100"
x-data="{ updated: false }" x-on:profile-updated="updated = true">
    <div class="w-full py-3 px-5 md:px-24 h-screen scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100 pb-40 md:pb-20  overflow-y-scroll">
        <header class="">
            <h2 class="text-3xl font-extrabold text-gray-500">
                {{ __('Admin Settings') }}
            </h2>
            <p class=" text-sm text-gray-500">
                Manage your account settings and preferences
            </p>
        </header>

        <div class="space-y-12 w-100 mt-2 mb-10">
            <!--Profile Form content -->
            <div class="w-full" x-data="{ remove: true }" x-on:livewire-upload-error="$wire.error('Upload Failed')"
                x-on:livewire-upload-finish="$wire.success('Upload Successful')">
                <div class="overflow-hidden bg-white rounded-lg shadow-md shadow-neutral-700">
                    <div class="px-4 py-4 sm:px-6 bg-navy-blue">
                        <h3 class="text-lg font-medium leading-6 text-gray-100">Profile Information</h3>
                        <p class="mt-1 text-sm text-gray-100">
                            Update your account's profile information and email address.
                        </p>
                    </div>
                    <div class="border-t border-gray-400">
                        <form wire:submit="updateProfileInformation" class="px-4 py-5 space-y-6 bg-gray-100 sm:p-6">
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
                                                    class="shadow-sm text-black focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('firstname') border-red-500 @enderror">
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-3">
                                            <label for="lastname" class="block text-sm font-medium text-gray-700">
                                                Last name
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" wire:model.live="lastname" id="lastname"
                                                    autocomplete="family-name" required
                                                    class="shadow-sm text-black  focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('lastname') border-red-500 @enderror">
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-3">
                                            <label for="email" class="block text-sm font-medium text-gray-700">
                                                Email address
                                            </label>
                                            <div class="mt-1">
                                                <input type="email" wire:model.live="email" id="email"
                                                    autocomplete="email" required
                                                    class="shadow-sm text-black  focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm border-gray-300 rounded-md @error('email') border-red-500 @enderror">
                                            </div>
                                        </div>
                                    </div>


                                </div>

                                <div class="grid gap-2 mt-5 md:w-1/2 md:mt-0">
                                    <div class="space-y-2">
                                        <p class="font-extrabold text-gray-700 text-md">
                                            Avatar
                                        </p>
                                        <x-mary-file wire:model.live="avatarUpload"
                                            accept="image/png, image/jpeg, image/jpg">
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
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
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
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!--Password Form content -->
            <div class="md:col-span-3 lg:col-span-3 w-full">
                <div class="overflow-hidden bg-white rounded-lg shadow-md shadow-neutral-700">
                    <div class="px-4 py-4 sm:px-6 bg-navy-blue">
                        <h3 class="text-lg font-medium leading-6 text-gray-100">Account Settings</h3>

                    </div>
                    <div class="flex border-t border-gray-400 justify-evenly">
                        <form wire:submit.prevent="updatePassword" class="w-1/2 px-4 py-5 space-y-6 bg-gray-200 sm:p-6">
                            <div class="grid gap-10">
                                <div class="w-100">
                                    <div class="grid md:grid-cols-6 gap-y-6 gap-x-4">
                                        <div class="sm:col-span-3">
                                            <label for="current_password"
                                                class="block text-sm font-medium text-gray-700">
                                                Current Password
                                            </label>
                                            <div class="mt-1 sm:col-span-3">
                                                <input type="password" wire:model="current_password"
                                                    id="current_password"
                                                    class="shadow-sm text-black focus:ing-navy-blue focus:border-navy-blue block w-full sm:text-sm @if ($errors->get('current_password')) border-red-500
                                        @else
                                            border-gray-300 @endif rounded-md">
                                                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                                            </div>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="password" class="block text-sm font-medium text-gray-700">
                                                Password
                                            </label>
                                            <div class="mt-1">
                                                <input type="password" wire:model="password" id="password"
                                                    class="shadow-sm text-black focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm  @if ($errors->get('password')) border-red-500
                                        @else
                                            border-gray-300 @endif rounded-md">
                                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                            </div>
                                        </div>

                                        <div class="sm:col-span-6">
                                            <label for="confirm_password"
                                                class="block text-sm font-medium text-gray-700">
                                                Confirm Password
                                            </label>
                                            <div class="mt-1">
                                                <input type="password" wire:model="password_confirmation"
                                                    id="confirm_password"
                                                    class="shadow-sm text-black focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm  @if ($errors->get('password_confirmation')) border-red-500
                                        @else
                                            border-gray-300 @endif rounded-md">
                                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                            </div>
                                        </div>


                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit"
                                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md shadow-sm bg-navy-blue hover:bg-navy-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-blue">
                                        Save
                                    </button>
                                    @session('profile_updated')
                                        <div
                                            class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-green-700 bg-green-100 rounded-md">
                                            Profile updated successfully!
                                        </div>
                                    @endsession
                                </div>

                            </div>

                        </form>
                        <section class="w-1/2 pt-5 space-y-6 bg-gray-200">
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 ">
                                    {{ __('Delete Account') }}
                                </h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
                                </p>
                            </header>

                            <x-danger-button class="w-36 " x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('Delete Account') }}</x-danger-button>

                            <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
                                <form wire:submit="deleteUser" class="p-6">

                                    <h2 class="text-lg font-medium text-gray-900 ">
                                        {{ __('Are you sure you want to delete your account?') }}
                                    </h2>

                                    <p class="mt-1 text-sm text-gray-600 ">
                                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                                    </p>

                                    <div class="mt-6">
                                        <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                                        <x-text-input wire:model="password" name="password" type="password"
                                            class="block text-white w-3/4 mt-1" placeholder="{{ __('Password') }}" />

                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div class="flex justify-end mt-6">
                                        <x-secondary-button x-on:click="$dispatch('close')">
                                            {{ __('Cancel') }}
                                        </x-secondary-button>

                                        <x-danger-button class="ms-3">
                                            {{ __('Delete Account') }}
                                        </x-danger-button>
                                    </div>
                                </form>
                            </x-modal>
                        </section>
                    </div>
                </div>
            </div>

        </div>
    <x-footer/>
    </div>

</div>
