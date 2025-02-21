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
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        #nprogress .bar {
            height: 8px !important;
            background-color: white !important;
            filter: invert(100%);
        }
    </style>
</head>

<body x-data="" class="font-sans text-gray-900 antialiased">
    <div class="w-full h-screen lg:flex justify-stretch m-0 border-0 ">
        @if (request()->routeIs('email.verification'))
            <div class=" md:w-full lg:w-100 bg-white dark:bg-gray-800  overflow-hidden h-100 px-7 py-7">
                {{ $slot }}
            </div>
        @else
            <div class=" bg-navy-blue py-32 w-1/2 hidden  shadow-md overflow-hidden  md:h-100 lg:flex justify-center">
                <img class="h-96 w-96 border-0" src="{{ asset('assets/twellr-logo.png') }}" alt="">
            </div>
            <div class=" md:w-full lg:w-1/2 bg-white dark:bg-gray-800  overflow-hidden h-100 px-7 py-7">
                {{ $slot }}
            </div>
        @endif

    </div>
</body>

</html>
