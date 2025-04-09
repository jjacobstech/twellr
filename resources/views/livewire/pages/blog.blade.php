   <?php

   use Livewire\Volt\Component;
   use function Livewire\Volt\layout;
   layout('layouts.app');

   ?>
   <div class=" pb-20 h-screen bg-white overflow-y-scroll">
       <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 h-screen pb-24">
           <!-- Blog Header -->
           <div class="flex justify-between items-center mb-4 sm:mb-6 top-0 bg-white z-10 py-2">
               <h1 class="text-2xl sm:text-3xl font-medium text-gray-700">Blog</h1>
           </div>

           <!-- Categories and Search - Sticky on mobile, fixed on desktop -->
           <div
               class="bg-gray-100 p-3 sm:p-4 rounded-lg flex flex-col md:flex-row justify-between items-center mb-6 sm:mb-8 top-14 z-10">
               <!-- Categories - Scrollable on small screens -->
               <div class="flex space-x-2 mb-4 md:mb-0 overflow-x-auto pb-2 w-full md:w-auto whitespace-nowrap">
                   <button
                       class="@if (url()->current() == route('blog')) bg-white @endif text-gray-600 px-3 py-1 rounded-full text-sm shadow-sm border border-gray-200 hover:bg-gray-50 flex-shrink-0">How
                       to</button>
                   <button
                       class="@if (url()->current() == 'blog') ) bg-white shadow-md @endif text-gray-600 px-3 py-1 rounded-full text-sm hover:bg-white hover:shadow-sm   flex-shrink-0">Business
                       Tips</button>
                   <button
                       class="@if (url()->current() == 'blog') ) bg-white shadow-md @endif text-gray-600 px-3 py-1 rounded-full text-sm hover:bg-white hover:shadow-sm   flex-shrink-0">What
                       is</button>

               </div>

               <!-- Search -->
               <div class="relative w-full md:w-auto">
                   <input type="text" placeholder="Search creator/design"
                       class="pl-4 pr-10 py-2 bg-white rounded-full border border-gray-200 w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                   <button class="absolute right-3 top-2">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="none"
                           viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                               d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                       </svg>
                   </button>
               </div>
           </div>

           <!-- Blog Posts - Main scrollable content -->
           <div class="space-y-6 sm:space-y-8 pb-24 ">
               <!-- First Blog Post -->
               <div
                   class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                   <div class="w-full md:w-1/3 h-56 sm:h-64 md:h-auto">
                       <!-- Person with beige/tan outfit and cap -->
                       <div class="w-full h-full bg-amber-100">
                           <img src="{{ asset('assets/pexels-godisable-jacob-226636-794064.jpg') }}"
                               alt="Woman in beige outfit with cap" class="w-full h-full object-cover" />
                       </div>
                   </div>
                   <div class="w-full md:w-2/3 p-4 sm:p-6 bg-gray-50">
                       <h2 class="text-xl sm:text-2xl font-medium text-gray-700 mb-2 sm:mb-3">Godisable Jacob</h2>
                       <p class="text-gray-500 mb-4 sm:mb-5 text-sm sm:text-base">
                           Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing
                           industries
                           for previewing layouts and visual mockups.
                       </p>
                       <a href="#"
                           class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 sm:px-6 py-2 rounded transition duration-200 text-sm sm:text-base">READ
                           MORE</a>
                   </div>
               </div>

               <!-- Second Blog Post -->
               <div
                   class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                   <div class="w-full md:w-1/3 h-56 sm:h-64 md:h-auto">
                       <!-- Person with yellow clothing against purple background -->
                       <div class="w-full h-full bg-purple-300">
                           <img src="{{ asset('assets/pexels-rfera-432059.jpg') }}"
                               alt="Woman in yellow outfit with purple background" class="w-full h-full object-cover" />
                       </div>
                   </div>
                   <div class="w-full md:w-2/3 p-4 sm:p-6 bg-gray-50">
                       <h2 class="text-xl sm:text-2xl font-medium text-gray-700 mb-2 sm:mb-3">Revera Gandenna</h2>
                       <p class="text-gray-500 mb-4 sm:mb-5 text-sm sm:text-base">
                           Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing
                           industries
                           for previewing layouts and visual mockups.
                       </p>
                       <a href="#"
                           class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 sm:px-6 py-2 rounded transition duration-200 text-sm sm:text-base">READ
                           MORE</a>
                   </div>
               </div>

               <!-- Add more blog posts as needed for scrolling demonstration -->
               <div
                   class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                   <div class="w-full md:w-1/3 h-56 sm:h-64 md:h-auto">
                       <div class="w-full h-full bg-blue-100">
                           <img src="{{ asset('assets/pexels-godisable-jacob-226636-794064.jpg') }}" alt="Blog image"
                               class="w-full h-full object-cover" />
                       </div>
                   </div>
                   <div class="w-full md:w-2/3 p-4 sm:p-6 bg-gray-50">
                       <h2 class="text-xl sm:text-2xl font-medium text-gray-700 mb-2 sm:mb-3">Godisable Jacob
                       </h2>
                       <p class="text-gray-500 mb-4 sm:mb-5 text-sm sm:text-base">
                           Lorem ipsum is placeholder text commonly used in the graphic, print, and publishing
                           industries
                           for previewing layouts and visual mockups.
                       </p>
                       <a href="#"
                           class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 sm:px-6 py-2 rounded transition duration-200 text-sm sm:text-base">READ
                           MORE</a>
                   </div>
               </div>
           </div>
       </div>
   </div>
