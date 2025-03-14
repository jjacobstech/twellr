   <?php

   use Livewire\Volt\Component;
   use Livewire\Attributes\Layout;
   use Illuminate\Support\Facades\Auth;

   new #[Layout('layouts.app')] class extends Component {
       public string $facebook = 'https://www.facebook.com';
       public string $twitter = 'https://www.x.com';
       public string $instagram = 'https://www.instagram.com';
       public string $whatsapp = 'https://web.whatsapp.com';
       public string $balance = '$300,000';
       public string $transactions = '';
       public $page = 5;

       public function adFunds() {}

       public function withdraw() {}
   }; ?>
   <div class="px-16 bg-white h-screen" x-data="{
       current: true
   }">
       <header class="">
           <h2 class="py-7 text-3xl font-extrabold text-gray-500">
               {{ __('Blog') }}
           </h2>
       </header>
       <div class=" py-4 bg-gray-100 p-5 w-100 rounded-[14px] text-center items-center justify-center mb-2 flex">
           <div class="w-1/2 grid justify-start">
               <p class="grid justify-start text-gray-400 pb-3">Current Balance</p>
               <p class="grid justify-start text-4xl font-extrabold text-gray-700 hover:scale-110 duration-500">
                   {{ $balance }}</p>
           </div>
           <div class="w-1/2 flex justify-end gap-5 font-bold">

           </div>
       </div>
       <h1 class="px-5 py-2 text-xl font-semibold text-left  text-gray-500 bg-gray-100 rounded-t-[14px]">
           Trasaction History

       </h1>

       {{-- <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
           aria-label="Table navigation">
           <span
               class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">Showing
               <span class="font-semibold text-gray-900 dark:text-white">1-10</span> of <span
                   class="font-semibold text-gray-900 dark:text-white">1000</span></span>
           <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
               <li>
                   <a href="#"
                       class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-navy-blue hover:text-white">Previous</a>
               </li>
               <li>
                   <a href="#"
                       x-bind:class="{{ $page == 1 }} ? ' bg-navy-blue text-white hover:bg-white hover:text-navy-blue' :
                           ' bg-white hover:bg-navy-blue hover:text-white'"
                       class="flex items-center justify-center px-3 h-8 leading-tight border-gray-300  border text-gray-500">1</a>
               </li>
               <li>
                   <a href="#"
                       x-bind:class="{{ $page == 2 }} ? ' bg-navy-blue text-white hover:bg-white hover:text-navy-blue' :
                           ' bg-white hover:bg-navy-blue hover:text-white'"
                       class="flex items-center justify-center px-3 h-8 leading-tight border-gray-300  border text-gray-500">2</a>
               </li>
               <li>
                   <a href="#"
                       x-bind:class="{{ $page == 3 }} ? ' bg-navy-blue text-white hover:bg-white hover:text-navy-blue' :
                           ' bg-white hover:bg-navy-blue hover:text-white'"
                       class="flex items-center justify-center px-3 h-8 leading-tight border-gray-300  border text-gray-500">3</a>
               </li>
               <li>
                   <a href="#"
                       x-bind:class="{{ $page == 4 }} ? ' bg-navy-blue text-white hover:bg-white hover:text-navy-blue' :
                           ' bg-white hover:bg-navy-blue hover:text-white'"
                       class="flex items-center justify-center px-3 h-8 leading-tight border-gray-300  border text-gray-500">4</a>
               </li>
               <li>
                   <a href="#"
                       x-bind:class="{{ $page == 5 }} ? ' bg-navy-blue text-white hover:bg-white hover:text-navy-blue' :
                           ' bg-white hover:bg-navy-blue hover:text-white'"
                       class="flex items-center justify-center px-3 h-8 leading-tight border-gray-300  border text-gray-500">5</a>
               </li>
               <li>
                   <a href="#"
                       class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-navy-blue hover:text-white">Next</a>
               </li>
           </ul>
       </nav> --}}

   </div>
