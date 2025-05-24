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

<body class="fixed w-screen h-screen font-sans antialiased bg-white">
    <x-mary-toast position="toast-top top-right" />

    <div wire:loading.class='hidden' class="h-full bg-gray-100">
        {{-- To Allow Admin view blog, marketplace and explore pages from admin panel --}}
        @if (Auth::user()->role === 'admin' || Auth::user()->role == 'admin')
            <livewire:layout.admin.navigation />
        @else
            <livewire:layout.navigation />
        @endif


        <!-- Page Content -->
        <main class="mt-1 h-screen md:h-full " x-cloak="display:none" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)"
            x-show="show" x-transition:enter="transition ease-in-out duration-500"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            {{ $slot }}
        </main>
        <div class="fixed bottom-0 left-0 right-0 z-50 bg-white shadow-sm">
            <x-footer />
        </div>
    </div>
 {{-- <!--Start of Tawk.to Script
    -->
        <script type="text/javascript">
            let customerSupport = document.getElementById('customer_support');


            customerSupport.addEventListener('click', () => {
                var Tawk_API = Tawk_API || {},
                    Tawk_LoadStart = new Date();

                (function() {
                    var s1 = document.createElement("script"),
                        s0 = document.getElementsByTagName("script")[0];
                    s1.async = true;
                    s1.src = 'https://embed.tawk.to/67db0589c029cf190fdd8e20/1imnor8sp';
                    s1.charset = 'UTF-8';
                    s1.setAttribute('crossorigin', '*');
                    s0.parentNode.insertBefore(s1, s0);

                })();
            });
        </script>
        <!--End of Tawk.to Script--> --}}
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
</body>

</html>
