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

    <!-- Favicon and Icons -->
    <link rel="shortcut icon" href="{{ asset('assets/twellr.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">



    <!-- UI Libraries - Load in correct order -->
    {{-- <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" /> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
     <!-- Scripts -->
    @bukStyles(true)

    <!-- Scripts needed in head -->
    @bukScripts(true)

</head>

<body class="h-screen overflow-hidden font-sans antialiased bg-white" x-data>
    <x-mary-spotlight shortcut="meta.slash" search-text="Search by: Creator, Design, Location, Ratings"
        no-results-text="Ops! Nothing here." class="bg-white" />

    <div class="bg-gray-100 ">
        <livewire:layout.navigation />

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="mt-2">
            {{ $slot }}
        </main>
    </div>

    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
</body>
</html>
