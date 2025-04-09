<x-app-layout>

    <div class=" bg-white  sm:px-8 lg:px-16 h-screen">
        <div class="w-full max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="py-4 sm:py-6 md:py-2">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-500">
                    {{ __('Support') }}
                </h2>
            </header>

            <div
                class="py-8 sm:py-12 md:py-16 px-6 sm:px-10 md:px-20 w-full bg-gray-100 rounded-lg sm:rounded-xl mt-4 flex flex-col items-center sm:items-start justify-center">
                <p class="text-xl sm:text-2xl font-bold w-full md:w-[85%] lg:w-[75%] text-center sm:text-left">
                    If you have questions or need additional information, do not hesitate to contact us. We have an
                    unbeatable support system so you can contact us via our chat using the button below.
                </p>

                <div
                    class="my-6 sm:my-8 md:my-10 font-extrabold text-xl sm:text-2xl flex flex-col gap-1 text-center sm:text-left">
                    <p class="text-golden">Live Chat Support</p>
                    <p>Click the button to chat with our support agent</p>
                </div>

                <div class="w-full flex justify-center sm:justify-start">
                    <x-bladewind.button id="customer_support"
                        class="uppercase py-2 sm:py-3 px-4 sm:px-5 text-white bg-golden hover:bg-golden/90 transition-colors rounded-lg sm:rounded-xl font-bold focus:bg-navy-blue flex items-center justify-center"
                        type="bg-golden">
                        <span class="flex items-center">
                            <img class="w-8 h-8 sm:w-10 sm:h-10" src="{{ asset('assets/tawk.png') }}" alt="Chat icon">
                            <span id="spinner" class="ml-2 text-base sm:text-lg">Chat with Twellr Support</span>
                        </span>
                    </x-bladewind.button>
                </div>
            </div>
        </div>

        <!--Start of Tawk.to Script
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
        <!--End of Tawk.to Script-->
    </div>
</x-app-layout>
