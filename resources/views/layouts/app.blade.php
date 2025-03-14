<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />

    <link rel="shortcut icon" href="{{ asset('assets/twellr.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php

@endphp

<body class="p-0 m-0 overflow-hidden font-sans antialiased border-0">
    <div class="p-0 m-0 bg-gray-100 border-0 ">
        <livewire:layout.navigation />
        {{-- <div wire:offline>
            <x-bladewind.alert type='info'>Offline</x-bladewind.alert>
        </div>
        <div wire:online>
            <x-bladewind.alert type='info' wire:online>Online</x-bladewind.alert>
        </div> --}}

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="p-0 mt-2 border-0">

            {{ $slot }}
        </main>
    </div>
</body>
<script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>

</html>
