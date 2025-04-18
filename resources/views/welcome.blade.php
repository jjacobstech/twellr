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

<body class="h-screen font-sans bg-gray-100">
    {{-- <x-mary-spotlight shortcut="meta.slash" search-text="Find Creators" no-results-text="Ops! Nothing here." /> --}}

    <livewire:welcome.navigation>
        <div class="p-6 mt-1 bg-white  container-fluid md:p-10 lg:p-10 lg:pb-20 h-full">
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
                        alt="Co-creation Example" class="object-contain w-full h-64 cursor-pointer"
                        @click="showModal = true">
                    {{-- <div x-show="showModal"
                        class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 backdrop-blur-md"
                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-xl">
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
                                                class="block mb-2 text-sm font-bold text-gray-700">Your
                                                Design Idea:</label>
                                            <textarea id="design-idea"
                                                class="w-full px-3 py-2 leading-tight text-gray-700 border rounded shadow appearance-none focus:outline-none focus:shadow-outline"
                                                rows="3" placeholder="Suggest a color, pattern, or detail..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="button"
                                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                        @click="showModal = false">
                                        Submit Idea
                                    </button>
                                    <button type="button"
                                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
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
            <footer class="bg-white shadow">
        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <p class="text-center text-gray-500">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
