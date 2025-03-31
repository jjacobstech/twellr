<x-app-layout>

    <div class=" bg-white  sm:px-8 lg:px-16 h-screen">
        <div class="w-full">
            <header class="">
                <h2 class="py-4 text-3xl font-extrabold text-gray-500">
                    {{ __('Support') }}
                </h2>

            </header>
            <div class=" py-16 px-20 w-100 bg-gray-100 rounded-[14px] mt-5-center items-center justify-center grid">
                <p class="text-2xl font-bold w-[75%]">If you questions or need additional information, do not hesitate
                    to
                    contact
                    us. We have an unbeatable
                    support system so you can contact us via our chat using the button below.</p>
                <div class="my-10 font-extrabold text-2xl grid gap-1">
                    <p class="text-golden">Live Chat support</p>
                    <p> Click the button to chat with our support agent</p>
                </div>
                <div>
                    <x-bladewind.button id="customer_support"
                        class="uppercase py-3 px-5 text-white bg-golden rounded-xl font-bold focus:bg-navy-blue flex"
                        type="bg-golden">
                        <span class="pt-2 flex"> <img class="w-10 h-10 " src="{{ asset('assets/tawk.png') }}"
                                alt=""><span id="spinner" class="pt-1 mx-1 text-lg">Chat with Twellr
                                Support </span></span></x-bladewind.button>
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
