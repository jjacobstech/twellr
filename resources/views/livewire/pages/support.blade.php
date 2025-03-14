   <?php

   use Livewire\Volt\Component;
   use Livewire\Attributes\Layout;
   use Illuminate\Support\Facades\Auth;

   new #[Layout('layouts.app')] class extends Component {
       public string $facebook = 'https://www.facebook.com';
       public string $twitter = 'https://www.x.com';
       public string $instagram = 'https://www.instagram.com';
       public string $whatsapp = 'https://web.whatsapp.com';
   }; ?>
   <div class="py-1 bg-white  sm:px-8 lg:px-16" x-data="{
       support: true,
       contact: false,
       backButton: false
   }">
       <div class="w-full">
           <header class="">
               <h2 x-show="support" x-cloak="display:hidden" x-transition:enter.duration.500ms
                   class="py-4 text-3xl font-extrabold text-gray-500">
                   {{ __('Support') }}
               </h2>
               <h2 x-show="contact" x-cloak="display:hidden" x-transition:enter.duration.500ms
                   class="py-4 text-3xl font-extrabold text-gray-500 w-100 text-center">
                   {{ __('Contact') }}
               </h2>
           </header>
           <div x-show="support" x-cloak="display:hidden" x-transition:enter.duration.500ms
               class=" py-16 px-20 w-100 bg-gray-100 rounded-[14px] mt-5-center items-center justify-center grid">
               <p class="text-2xl font-bold w-[75%]">If you questions or need additional information, do not hesitate to
                   contact
                   us. We have an unbeatable
                   support system so you can contact us via our chat using the button below.</p>
               <div class="my-10 font-extrabold text-2xl grid gap-1">
                   <p class="text-golden">Live Chat support</p>
                   <p> Click the button to chat with our support agent</p>
               </div>
               <div>
                   <button @click="support = false, contact = true, backButton = true"
                       class="uppercase p-5 text-white bg-golden rounded-xl font-bold">Chat with Twellr
                       Support</button>
               </div>

           </div>

       </div>
       <div x-show="contact" x-cloak="display:hidden" x-transition:enter.duration.500ms
           class=" py-10 px-10 w-[50%] mx-[25%] bg-gray-100 rounded-[14px]  grid justify-center gap-5 ">

           <!-- Facebook -->
           <a href="{{ $facebook }}" target="_blank">
               <button type="button" data-twe-ripple-init data-twe-ripple-color="light"
                   class="mb-2 inline-block rounded bg-[#1877f2] px-6 py-2.5 text-xs font-medium uppercase leading-normal text-white shadow-md transition duration-500 ease-in-out hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0 active:shadow-lg hover:scale-125 transform">
                   <span class="[&>svg]:h-4 [&>svg]:w-4">
                       <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 320 512">
                           <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc. -->
                           <path
                               d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z" />
                       </svg>
                   </span>
               </button>
           </a>

           <!-- Instagram -->
           <a href="{{ $instagram }}" target="_blank">
               <button type="button" data-twe-ripple-init data-twe-ripple-color="light"
                   class="mb-2 inline-block rounded bg-[#c13584] px-6 py-2.5 text-xs font-medium uppercase leading-normal text-white shadow-md transition duration-500 ease-in-out hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0 active:shadow-lg hover:scale-125 transform">
                   <span class="[&>svg]:h-4 [&>svg]:w-4">
                       <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
                           <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc. -->
                           <path
                               d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                       </svg>
                   </span>
               </button>
           </a>

           <!-- X -->
           <a href="{{ $twitter }}" target="_blank">
               <button type="button" data-twe-ripple-init data-twe-ripple-color="light"
                   class="mb-2 inline-block rounded bg-black px-6 py-2.5 text-xs font-medium uppercase leading-normal text-white shadow-md transition duration-500 ease-in-out hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0 active:shadow-lg hover:scale-125 transform">
                   <span class="[&>svg]:h-4 [&>svg]:w-4">
                       <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
                           <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc. -->
                           <path
                               d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                       </svg>
                   </span>
               </button>
           </a>

           <!-- Whatsapp -->
           <a href="{{ $whatsapp }}" target="_blank">
               <button type="button" data-twe-ripple-init data-twe-ripple-color="light"
                   class="mb-2 inline-block rounded bg-[#128c7e] px-6 py-2.5 text-xs font-medium uppercase leading-normal text-white shadow-md transition duration-500 ease-in-out hover:shadow-lg focus:shadow-lg focus:outline-none focus:ring-0 active:shadow-lg hover:scale-125 transform">
                   <span class="[&>svg]:h-4 [&>svg]:w-4">
                       <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 448 512">
                           <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc. -->
                           <path
                               d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                       </svg>
                   </span>
               </button>
           </a>

           <div class=" w-full mt-3">
               <img x-show="backButton" x-transition:enter.duration.500ms x-cloak="display:none"
                   @click="support = true, contact = false, backButton = false"
                   class="w-16 h-16  transition hover:scale-125" src="{{ asset('assets/back-arrow.png') }}"
                   alt="">
           </div>
       </div>
   </div>
