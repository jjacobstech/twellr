<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public function uploadDesignStack() {}
    public function uploadPrintableStack() {}
}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,

}">
    <div class="flex gap-1 w-[100%]">
        <x-market-place-sidebar class="[20%]" />
        <div class="bg-white p-4 w-[80%]">
            <div class="grid h-full w-full gap-10 px-5  grid-col-6 md:grid-cols-4 sm:grid-cols-2 relative ">
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
