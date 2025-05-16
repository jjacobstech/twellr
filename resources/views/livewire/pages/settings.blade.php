<?php
use function Livewire\Volt\layout;
layout('layouts.app');
?>
<div class="bg-white h-screen w-screen" x-data="{ updated: false }" x-on:profile-updated="updated = true">
    <div class="w-full py-3 px-5 md:px-24 h-screen overflow-y-scroll mb-40 pb-20  scrollbar-none">
        <div wire:loading
            class="absolute py-3 mb-6 text-white transition-opacity duration-500 border rounded alert-info alert top-5 right-1 bg-navy-blue border-navy-blue"
            role="alert">
            <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg> Loading . . .
        </div>
        <header class="">
            <h2 class="text-3xl font-extrabold text-gray-500">
                {{ __('User  Settings') }}
            </h2>
            <p class=" text-sm text-gray-500">
                Manage your account settings and preferences
            </p>
        </header>

        <div class="space-y-12 w-100 mt-2 mb-10">
            <livewire:account.update-profile-information-form />

            <livewire:account.update-password-form />
   <x-footer/>
        </div>

    </div>
</div>
