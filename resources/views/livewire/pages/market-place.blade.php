<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,

}">
    <div class="flex gap-1 w-[100%]">
        <x-market-place-sidebar class="" />
        <div class="relative bg-white p-4 w-screen md:w-[80%] h-screen">
            <div
                class="relative grid w-full h-full gap-5 px-10 overflow-y-scroll grid-col-6 md:grid-cols-4 py-8 sm:grid-cols-2 ">
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />
                <x-product-card wire:navigate />

                {{-- @if ($projects->isEmpty())
                        <div class="flex justify-center ">
                            <h1 class='w-full mt-40 ml-10 text-5xl font-bold text-center'>No Project</h1>
                        </div>
                    @else
                        @foreach ($projects as $project)
                            <x-product-card wire:navigate />
                        @endforeach
                    @endif --}}
            </div>
        </div>

    </div>
