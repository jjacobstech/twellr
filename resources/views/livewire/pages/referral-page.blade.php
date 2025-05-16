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
    <style>
        .bg-navy-blue {
            background-color: #1a365d;
        }

        .bg-golden {
            background-color: #d4af37;
        }

        .hover\:bg-golden:hover {
            background-color: #d4af37;
        }

        .text-navy-blue {
            color: #1a365d;
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
        }
    </style>
</head>

<body class="bg-gray-50">
    <nav class="bg-white shadow-md">
        <div class="container flex items-center justify-between px-6 py-3 mx-auto">
            <div class="flex items-center shrink-0">
                <a class="flex" href="{{ route('home') }} " wire:navigate>
                    <x-application-logo class="block w-auto fill-current h-9" />
                    <img class="hidden md:h-5 md:px-3 md:my-1 md:block" src="{{ asset('assets/twellr-text.png') }}"
                        alt="">
                </a>
            </div>
            <div class="items-center hidden space-x-4 md:flex">
                <a href="{{ route('login') }}"
                    class="px-4 py-2 font-semibold text-white transition-colors rounded-md bg-navy-blue hover:bg-golden">Login</a>
            </div>
            <div class="md:hidden">
                <button class="text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="container p-6 mx-auto mt-1 bg-white md:p-10 lg:p-10 lg:pb-20">
        <div class="mb-8 text-center">

            <h1 class="mb-3 text-4xl font-bold text-gray-800 md:text-5xl" id="mainHeading">
                Stay Creative. Monetize your designs.</h1>
            <p class="mb-2 text-xl font-bold text-gray-600 md:text-2xl">Co-create your fashion.
                Have a say in what you wear!</p>
            <p class="mb-6 text-lg text-gray-500">You've been invited to join our creative
                community!</p>

            <div x-data="{ showConfetti: false }" class="relative">
                <a href="{{ route('register', ['referral' => $referral_link]) }}">
                    <button
                        class="px-8 py-3 text-lg font-semibold text-white transition-colors rounded-md shadow-lg pulse bg-navy-blue hover:bg-golden">
                        Accept Your Invitation
                    </button>
                </a>


            </div>

            <div class="flex justify-center mt-8 space-x-6">
                <div class="flex items-center text-gray-700">
                    <svg class="w-5 h-5 mr-2 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>100% Free to Join</span>
                </div>
                <div class="flex items-center text-gray-700">
                    <svg class="w-5 h-5 mr-2 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Earn from Your Ideas</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 mb-12 md:grid-cols-2">
            <div
                class="relative h-64 overflow-hidden transition-all transform bg-yellow-100 rounded-lg shadow-md hover:scale-105">

                <img src="{{ asset('assets/hoodie-front-back-mockup_1247017-29.jpg') }}" alt="New Sweater Design"
                    class="object-cover w-full h-64">

            </div>

            <div class="h-64 overflow-hidden transition-all transform bg-white rounded-lg shadow-md hover:scale-105">
                <img src="{{ asset('assets/white-sweater-png-sticker-design-space-transparent-background_53876-988335.jpg') }}"
                    alt="Co-creation Example" class="object-contain w-full h-64 cursor-pointer"
                    @click="showModal = true">
            </div>
        </div>

        <div class="p-8 mb-12 rounded-lg bg-gray-50">
            <h2 class="mb-6 text-2xl font-bold text-center text-gray-800">How It Works</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-navy-blue floating" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="currentColor" stroke-width="2" />
                            <path d="M12 16V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path d="M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">1. Share Your Ideas</h3>
                    <p class="text-gray-600">Submit your fashion design ideas and concepts to
                        our community.</p>
                </div>
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-navy-blue floating" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" style="animation-delay: 0.5s">
                            <path
                                d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M8.5 11C10.7091 11 12.5 9.20914 12.5 7C12.5 4.79086 10.7091 3 8.5 3C6.29086 3 4.5 4.79086 4.5 7C4.5 9.20914 6.29086 11 8.5 11Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M20 8V14" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M23 11H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">2. Collaborate & Vote</h3>
                    <p class="text-gray-600">Collaborate with others and vote on designs to be
                        produced.</p>
                </div>
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-navy-blue floating" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" style="animation-delay: 1s">
                            <path d="M12 1V23" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path
                                d="M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-800">3. Earn Rewards</h3>
                    <p class="text-gray-600">Earn a percentage of sales when your designs are
                        produced and sold.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-white bg-navy-blue">
        <div class="container px-6 py-6 mx-auto">
                <div class="mb-6 md:mb-0">
                    <div class="flex flex-between w-full">
                        <x-bladewind.avatar class="border-0 ring-0" size="big"
                            image="{{ asset('assets/twellr-logo.png') }}" alt="" />
                        <span class="text-xl font-bold  my-4">Twellr</span>

                    </div>

                    <p class="mt-2 text-sm text-white">Co-create your fashion. Have a say in
                        what you wear!</p>
                    <p class="text-sm  text-white">Copyright © 2025 Twellr. All Rights Reserved</p>
                </div>
        </div>
    </footer>
</body>

</html>
