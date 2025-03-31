<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
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
    }
}; ?>
<!--Password Form content -->
<div class="md:col-span-3  lg:col-span-3">
    <div class="bg-white overflow-hidden rounded-lg shadow-neutral-700 shadow-md">
        <div class="px-4 py-4 sm:px-6 bg-navy-blue">
            <h3 class="text-lg font-medium leading-6 text-gray-100">Account Settings</h3>

        </div>
        <div class="border-t border-gray-400 flex justify-evenly">
            <form wire:submit.prevent="updatePassword" class="px-4 py-5 sm:p-6 space-y-6 bg-gray-200 w-1/2">
                <div class="grid gap-10">
                    <div class="w-100">
                        <div class=" grid md:grid-cols-6 gap-y-6 gap-x-4">
                            <div class="sm:col-span-3">
                                <label for="current_password" class="block text-sm font-medium text-gray-700">
                                    Current Password
                                </label>
                                <div class="mt-1 sm:col-span-3">
                                    <input type="password" wire:model="current_password" id="current_password"
                                        autocomplete="current_ password"
                                        class="shadow-sm focus:ing-navy-blue focus:border-navy-blue block w-full sm:text-sm @if ($errors->get('current_password')) border-red-500
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
                                    <input type="password" wire:model="password" id="password" autocomplete="password"
                                        class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm  @if ($errors->get('password')) border-red-500
                                        @else
                                            border-gray-300 @endif rounded-md">
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                                    Confirm Password
                                </label>
                                <div class="mt-1">
                                    <input type="password" wire:model="confirm_password" id="confirm_password"
                                        autocomplete="password"
                                        class="shadow-sm focus:ring-navy-blue focus:border-navy-blue block w-full sm:text-sm  @if ($errors->get('password_confirmation')) border-red-500
                                        @else
                                            border-gray-300 @endif rounded-md">
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
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

                </div>

            </form>
            <livewire:account.delete-user-form />
        </div>
    </div>

</div>
