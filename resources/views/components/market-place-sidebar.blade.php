@props(['locations', 'categories', 'filterTerm'])

<!-- Desktop Sidebar -->
<div id="sidebar" x-cloak="display: none"
     class="relative hidden transition-transform duration-300 ease-in-out bg-white shadow-xl sm:hidden md:block md:w-[28%] lg:w-[20%] h-full">
    <div class="px-5 py-3 text-2xl font-bold border-b-2 text-golden">
        All Categories
    </div>

    <div class="px-10 mt-6 space-y-3 font-semibold text-gray-600 text-md">
        @foreach ($categories as $category)
            <span wire:click="filter('{{ $category->filter }}')"
                  class="flex items-center gap-2 capitalize transition cursor-pointer hover:text-navy-blue">
                @svg('eva-list', ['class' => 'w-5 h-5 text-gray-500'])
                {{ $category->title }}
            </span>
        @endforeach
    </div>

    <!-- Location Filter -->
    <div class="pl-5 pr-6 mt-10 font-semibold text-gray-600 text-md">
        <p class="mb-2">Select Location:</p>
        <select wire:model="location"
                class="w-full p-2 border border-gray-400 rounded-md focus:ring-navy-blue focus:border-navy-blue">
            <option value="">All Locations</option>
            @forelse ($locations as $location)
                <option value="{{ $location->id }}">{{ $location->name }}</option>
            @empty
                <option value="">No Locations</option>
            @endforelse
        </select>
    </div>

    <!-- Contact Button -->
    <div class="px-5 mt-6">
        <x-bladewind.button id="customer_support"
            color="w-full px-4 py-3 font-bold uppercase transition duration-200 bg-navy-blue text-white hover:bg-golden hover:border-golden">
            Contact Our In-House Designer
        </x-bladewind.button>
    </div>
</div>

<!-- Mobile Bar -->
<div id="mobilebar"
     class="z-10 grid items-center justify-between grid-cols-3 gap-3 px-4 py-3 bg-white shadow-sm md:hidden">
    <select wire:model="filterTerm"
            class="px-3 py-2 text-sm text-gray-700 border border-gray-400 rounded-md focus:ring-navy-blue focus:border-navy-blue">
        <option value="">Filter by Category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->filter }}">{{ ucfirst($category->title) }}</option>
        @endforeach
    </select>

    <select wire:model="location"
            class="px-3 py-2 text-sm text-gray-700 border border-gray-400 rounded-md focus:ring-navy-blue focus:border-navy-blue">
        <option value="">Location</option>
        @foreach ($locations as $location)
            <option value="{{ $location->id }}">{{ $location->name }}</option>
        @endforeach
    </select>

    <x-bladewind.button id="customer_support"
        color="w-full px-3 py-2 font-bold uppercase transition duration-200 bg-navy-blue text-white hover:bg-golden hover:border-golden text-sm">
        Designer Support
    </x-bladewind.button>
</div>
