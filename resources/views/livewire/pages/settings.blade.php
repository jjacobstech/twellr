<?php
use function Livewire\Volt\layout;
 layout('layouts.app');
  ?>
<div class="bg-white h-screen w-screen" x-data="{ updated: false }" x-on:profile-updated="updated = true">
    <div class="w-full py-3 px-5 md:px-24 h-screen overflow-y-scroll mb-40 pb-20  scrollbar-none">
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

        </div>

    </div>
</div>
