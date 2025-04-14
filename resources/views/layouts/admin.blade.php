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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
     <!-- Scripts -->
    @bukStyles(true)

    <!-- Scripts needed in head -->
    @bukScripts(true)

</head>

<body class=" p-0 m-0 font-sans antialiased bg-gray-100 border-0 w-screen">

        <livewire:layout.admin.navigation />

        <!-- Page Content -->
        <main class="p-0 border-0 bg-gray-100 w-screen">
            {{ $slot }}
        </main>
    </div>
</body>
<script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>

</html>
