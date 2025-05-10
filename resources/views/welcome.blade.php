<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ asset('assets/twellr.svg') }}" type="image/x-icon">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen overflow-y-scroll font-sans bg-gray-100 scrollbar-none">

    <livewire:welcome.navigation />

    <div class=" px-4 sm:px-6 lg:px-8 py-10 bg-white mt-1 scrollbar-none pb-20">
        <!-- Hero Section -->
        <div class="mb-10 text-center">
            <h1 class="mb-3 text-3xl sm:text-4xl font-bold text-gray-800">Stay Creative. Monetize your designs.</h1>
            <p class="text-lg sm:text-2xl font-semibold text-gray-600">Co-create your fashion. Have a say in what you wear!</p>
            <a href="{{ route('login') }}">
                <button class="px-6 py-3 mt-6 text-white text-sm sm:text-base bg-navy-blue hover:bg-golden rounded-md transition-colors font-semibold">
                    Get Started Now
                </button>
            </a>
        </div>

        <!-- Image Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="relative overflow-hidden bg-yellow-200 rounded-md shadow-md">
                <div class="absolute top-3 left-3 bg-red-500 text-white font-semibold py-1 px-3 rounded-sm transform rotate-[-20deg]">
                    NEW
                </div>
                <img src="{{ asset('assets/hoodie-front-back-mockup_1247017-29.jpg') }}"
                     alt="New Sweater Design"
                     class="object-cover w-full h-60 sm:h-72 md:h-80 lg:h-96">
            </div>

            <div x-data="{ showModal: false }" class="overflow-hidden bg-white rounded-md shadow-md">
                <img src="{{ asset('assets/white-sweater-png-sticker-design-space-transparent-background_53876-988335.jpg') }}"
                     alt="Co-creation Example"
                     class="object-contain w-full h-60 sm:h-72 md:h-80 lg:h-96 cursor-pointer">
            </div>
        </div>

        <!-- Explore Section -->
        <div class="mt-10">
            <livewire:explore-section />
        </div>

        <!-- Footer -->
        <div class="pt-10 mt-10 border-t border-gray-200">
            <p class="text-center text-gray-500">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>

</body>

</html>
