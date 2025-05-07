<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ asset('assets/twellr.svg') }}" type="image/x-icon">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen overflow-hidden font-sans bg-gray-100">

    <livewire:welcome.navigation>
        <div class="h-full p-6 mt-1 bg-white container-fluid md:p-10 lg:p-10 lg:pb-20">
            <div class="mb-5 text-center">
                <h1 class="mb-2 text-4xl font-bold text-gray-800 md:text-4xl">Stay Creative. Monetize your designs.</h1>
                <p class="text-2xl font-bold text-gray-600">Co-create your fashion. Have a say in what you wear!</p>
                <a href="{{ route('login') }}">
                    <button
                        class="px-6 py-3 mt-6 font-semibold text-white transition-colors rounded-md bg-navy-blue hover:bg-golden">
                        Get Started Now
                    </button>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="relative overflow-hidden bg-yellow-200 rounded-md shadow-md">
                    <div
                        class="absolute top-3 left-3 bg-red-500 text-white font-semibold py-1 px-3 rounded-sm transform rotate-[-20deg]">
                        NEW
                    </div>
                    <img src="{{ asset('assets/hoodie-front-back-mockup_1247017-29.jpg') }}" alt="New Sweater Design"
                        class="object-cover w-full h-64">
                </div>

                <div x-data="{ showModal: false }" class="overflow-hidden bg-white rounded-md shadow-md">
                    <img src="{{ asset('assets/white-sweater-png-sticker-design-space-transparent-background_53876-988335.jpg') }}"
                        alt="Co-creation Example" class="object-contain w-full h-64 cursor-pointer">

                </div>
            </div>
                <div class="px-4 mx-auto mt-16 max-w-7xl sm:px-6 lg:px-8">
                    <p class="text-center text-gray-500 bg-transparent">© {{ date('Y') }} {{ config('app.name') }}. All rights
                        reserved.</p>
                </div>
        </div>

</body>

</html>
