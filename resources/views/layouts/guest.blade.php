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

    <link rel="shortcut icon" href="{{ asset('assets/twellr.svg') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @bukStyles(true)
    @bukScripts(true)

</head>

<body x-data="" class="font-sans antialiased text-gray-900 bg-white scrollbar-none h-screen">
    <div class="w-full h-full m-0 border-0 lg:flex justify-stretch  scrollbar-none rounded-lg">

        @if (request()->routeIs('email.verification') ||
                request()->routeIs('admin.email.verification') ||
                request()->routeIs('creative.payment.preference'))
            <div class="overflow-hidden bg-white md:w-full lg:w-100 h-100 px-7 py-7">
                {{ $slot }}
            </div>
        @else
            <div class="justify-center hidden w-1/2 py-32 overflow-hidden shadow-md bg-navy-blue md:h-100 lg:flex">
                <img class="border-0 h-96 w-96" src="{{ asset('assets/twellr-logo.png') }}" alt="">
            </div>
            <div class="scrollbar-none overflow-hidden bg-white md:w-full lg:w-1/2 h-100 px-12 py-7">
                {{ $slot }}
            </div>
        @endif

    </div>
</body>

</html>
