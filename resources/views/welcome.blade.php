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
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
</>

<body class="font-sans antialiased h-screen bg-gray-200">
    <x-mary-spotlight shortcut="meta.slash" search-text="Find Creators" no-results-text="Ops! Nothing here." />

    <livewire:welcome.navigation />

    <main class="mt-1 h-100 bg-white">
        <x-mary-card class="bg-blue-600 h-full">

        </x-mary-card>
    </main>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>

    {{-- navbar end --}}
</body>

</html>
