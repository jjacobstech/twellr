<x-app-layout>

    <div class="h-screen bg-white sm:px-8 lg:px-16" x-data="{

 chatLoaded: false,

    support() {
        if (this.chatLoaded) return;

        window.Tawk_API = window.Tawk_API || {};
        window.Tawk_LoadStart = new Date();

        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://embed.tawk.to/67db0589c029cf190fdd8e20/1imnor8sp';
        script.charset = 'UTF-8';
        script.setAttribute('crossorigin', '*');

        script.onload = () => {
            this.chatLoaded = true;
        };

        document.head.appendChild(script);
    }

    }">

    <p class="text-black" wire:loading >Loading . . . </p>
        <div class="w-full max-w-screen-xl px-4 mx-auto sm:px-6 lg:px-8">
            <header class="py-4 sm:py-6 md:py-2">
                <h2 class="text-2xl font-extrabold text-gray-500 sm:text-3xl">
                    {{ __('Support') }}
                </h2>
            </header>

            <div
                class="flex flex-col items-center justify-center w-full px-6 py-8 mt-4 bg-gray-100 rounded-lg sm:py-12 md:py-16 sm:px-10 md:px-20 sm:rounded-xl sm:items-start">
                <p class="text-xl sm:text-2xl font-bold w-full md:w-[85%] lg:w-[75%] text-center sm:text-left text-gray-500">
                    If you have questions or need additional information, do not hesitate to contact us. We have an
                    unbeatable support system so you can contact us via our chat using the button below.
                </p>

                <div
                    class="flex flex-col gap-1 my-6 text-xl font-extrabold text-center sm:my-8 md:my-10 sm:text-2xl sm:text-left">
                    <p class="text-golden">Live Chat Support</p>
                    <p class="text-gray-500" >Click the button to chat with our support agent</p>
                </div>

                <div class="flex justify-center w-full sm:justify-start">
                    <x-bladewind.button @click="support"
                        class="flex items-center justify-center px-4 py-2 font-bold text-white uppercase transition-colors rounded-lg sm:py-3 sm:px-5 bg-golden hover:bg-golden/90 sm:rounded-xl focus:bg-navy-blue active:bg-navy-blue "
                        type="bg-golden">
                        <span class="flex items-center">
                            <img class="w-8 h-8 sm:w-10 sm:h-10" src="{{ asset('assets/tawk.png') }}" alt="Chat icon">
                            <span id="spinner" class="ml-2 text-base sm:text-lg">Chat with Twellr Support</span>
                        </span>
                    </x-bladewind.button>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
