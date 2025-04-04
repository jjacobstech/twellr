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

<body class="bg-gray-100 font-sans h-screen">
    <x-mary-spotlight shortcut="meta.slash" search-text="Find Creators" no-results-text="Ops! Nothing here." />

    <livewire:welcome.navigation>
        <div class=" container-fluid p-6 md:p-10 lg:p-10 bg-white mt-1 lg:pb-20">
            <div class="text-center mb-5">
                <h1 class="text-4xl md:text-4xl font-bold text-gray-800 mb-2">Stay Creative. Monetize your designs.</h1>
                <p class="text-2xl text-gray-600 font-bold">Co-create your fashion. Have a say in what you wear!</p>
                <a href="{{ route('login') }}">
                    <button
                        class="bg-navy-blue hover:bg-golden transition-colors text-white font-semibold py-3 px-6 rounded-md mt-6">
                        Get Started Now
                    </button>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative bg-yellow-200 rounded-md overflow-hidden shadow-md">
                    <div
                        class="absolute top-3 left-3 bg-red-500 text-white font-semibold py-1 px-3 rounded-sm transform rotate-[-20deg]">
                        NEW
                    </div>
                    <img src="{{ asset('assets/hoodie-front-back-mockup_1247017-29.jpg') }}" alt="New Sweater Design"
                        class="w-full h-64 object-cover">
                </div>

                <div x-data="{ showModal: false }" class="bg-white rounded-md overflow-hidden shadow-md">
                    <img src="{{ asset('assets/white-sweater-png-sticker-design-space-transparent-background_53876-988335.jpg') }}"
                        alt="Co-creation Example" class="w-full h-64 object-contain cursor-pointer"
                        @click="showModal = true">
                    {{-- <div x-show="showModal"
                        class="fixed z-50 backdrop-blur-md inset-0 overflow-y-auto bg-gray-900 bg-opacity-50"
                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                                        Co-create this look!
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Imagine this hat in different colors or with custom embellishments. You can
                                            contribute your ideas!
                                        </p>
                                        <div class="mt-4">
                                            <label for="design-idea"
                                                class="block text-gray-700 text-sm font-bold mb-2">Your
                                                Design Idea:</label>
                                            <textarea id="design-idea"
                                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                rows="3" placeholder="Suggest a color, pattern, or detail..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="button"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                        @click="showModal = false">
                                        Submit Idea
                                    </button>
                                    <button type="button"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                                        @click="showModal = false">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
</body>

</html>
