<?php
use Mary\Traits\Toast;
use App\Models\Contest;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\FileHelper;
use function Livewire\Volt\{state, mount, layout, uses, with, usesPagination, usesFileUploads};

usesFileUploads();
layout('layouts.app');
usesPagination(view: 'vendor.livewire.tailwind', theme: 'simple');
uses(Toast::class);

state(['searchTerm' => '']);
state(['search' => '']);
state(['view' => null]);
state(['entry' => null]);
state(['categoryFilter' => '']);
state(['dateSort' => 'desc']);
state(['currentPage' => 1]);
state(['perPage' => 3]);
state(['categories' => fn() => Category::all()]);
state(['modal' => false]);
state(['voteModal' => false]);
state(['entryModal' => false]);
state(['designFest' => true]);
state(['whoRockedItBest' => false]);
state([
    'name' => '',
    'description' => '',
    'photo' => null,
]);

with([
    'designs' => fn() => Contest::orderBy('created_at', $this->dateSort)
        ->where(function ($query) {
            $query->where('id', 'like', "%{$this->searchTerm}%")->orWhereHas('product', function ($query) {
                $query
                    ->where('name', 'like', "%{$this->searchTerm}%")
                    ->where('category_id', 'like', "%{$this->categoryFilter}%")
                    ->orWhereHas('designer', function ($designer) {
                        $designer->where('name', 'like', "%{$this->searchTerm}%");
                    });
            });
        })
        ->where('category_id', 'like', "%$this->categoryFilter%")
        ->with('product', 'user', 'category')

        ->paginate($this->perPage),

    'entries' => fn() => Contest::orderBy('created_at', $this->dateSort)
        ->where('type', '=', 'who_rocked_it_best')
        ->where('name', 'like', "%{$this->search}%")
        ->where('description', 'like', "%{$this->search}%")
        ->with('user')
        ->paginate($this->perPage),
]);

$resetFilters = function () {
    $this->searchTerm = '';
    $this->categoryFilter = '';
};

// Method to toggle sort direction
$toggleSort = function () {
    $this->dateSort = $this->dateSort === 'desc' ? 'asc' : 'desc';
};

$toggleCategory = function ($id) {
    return $this->categoryFilter = $id;
};

$viewDesign = function ($id) {
    $this->view = Product::find($id);
    $this->modal = true;
};

$viewEntry = function ($id) {
    $this->entry = Contest::find($id);
    $this->entryModal = true;
};

$closeModal = function () {
    $this->modal = false;
    $this->voteModal = false;
    $this->entryModal = false;
    $this->view = null;
};


$closeEntryModal = function () {
    $this->entryModal = false;
    $this->view = null;
};

$vote = function ($id) {
    $this->view = Product::find($id);
    $this->voteModal = true;
};

$castVote = function ($id) {
    $this->voteModal = false;
    $this->view = null;
};
$setTab = function () {
    $this->designFest = !$this->designFest;
    $this->whoRockedItBest = !$this->whoRockedItBest;
};

$submitEntry = function () {
    $entry = (object) $this->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'photo' => 'required|image|mimes:' . config('twellr.printable_stack_format'),
    ]);

    $photo = FileHelper::getFileData($entry->photo);

    // Save Files Into Storage
    $entrySaved = FileHelper::saveFile($entry->photo, 'contest/' . $photo->name);

    if ($entrySaved) {
        $contestCreate = Contest::create([
            'name' => $entry->name,
            'description' => $entry->description,
            'photo' => $photo->name,
            'type' => 'who_rocked_it_best',
            'user_id' => Auth::id(),
        ]);

        if ($contestCreate) {
            $this->name = '';
            $this->description = '';
            $this->photo = '';

            $this->success('success', 'Your outfit has been submitted!');
        } else {
            $this->error('Unable to create contest entry');
        }
    } else {
        $this->error('Unable to save contest entry');
    }
};

?>
<div class="h-screen pb-20 overflow-y-scroll bg-gray-50 scrollbar-none" x-data="{
    designFest: true,
    whoRockedItBest: false,
    whoRockedItBestView: false,
    whoRockedItBestEntry: false,
    photo: null,
    setTab() {
        this.designFest = !this.designFest;
        this.whoRockedItBest = !this.whoRockedItBest;
        this.whoRockedItBestView = !this.whoRockedItBestView;
    }
}" x-init=" $dispatch('scroll-to-top');
 }">
    <div class="h-screen px-4 py-6 pb-24 mx-auto max-w-7xl sm:px-6 lg:px-8 sm:py-8">
        <!-- Flat Tabs with Bottom Border -->
        <div class="flex mb-4 space-x-6 w-50">
            <button @click="setTab()" class="w-1/4 pb-2 font-bold transition-all duration-300 text-md focus:outline-none"
                :class="designFest ? 'text-navy-blue border-b-2 border-navy-blue' :
                    'text-gray-500 hover:text-navy-blue border-b-2 border-transparent'">
                Design Fest
            </button>

            <button @click="setTab()" class="w-1/4 pb-2 font-bold transition-all duration-300 text-md focus:outline-none"
                :class="whoRockedItBest ? 'text-navy-blue border-b-2 border-navy-blue' :
                    'text-gray-500 hover:text-navy-blue border-b-2 border-transparent'">
                Who Rocked It Best
            </button>

            <div x-transition:enter.duration.500ms x-cloak="display:none" x-show="!designFest" class="flex justify-end w-1/2 gap-5">

                  <!-- Search -->
                <div class="relative w-full md:w-auto">
                    <input type="text" wire:model.live="search"
                        placeholder="Search Name/Description"
                        class="w-full py-2 pl-4 pr-10 text-gray-700 bg-white border border-indigo-200 rounded-full md:w-64 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <button class="absolute right-3 top-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                <button
                    @click="whoRockedItBestEntry = !whoRockedItBestEntry, whoRockedItBestView = !whoRockedItBestView"
                    class="flex-shrink-0 px-3 py-1 text-sm transition-all duration-300 scale-100 border rounded-full shadow-sm "
                    :class="whoRockedItBestEntry ?
                        'text-white bg-navy-blue border-navy-blue hover:bg-white hover:text-navy-blue ' :
                        'bg-white text-navy-blue border-indigo-200 hover:bg-indigo-50'">
                    Upload Your Outfit
                </button>
            </div>
        </div>
        <div x-transition:enter.duration.500ms x-cloak="display:none" x-show="designFest">

            <!-- Categories and Search - Sticky on mobile, fixed on desktop -->
            <div
                class="z-10 flex flex-col items-center justify-between p-3 mb-6 rounded-lg bg-indigo-50 sm:p-4 md:flex-row sm:mb-8 top-14">
                <!-- Categories - Scrollable on small screens -->
                <div class="flex w-full pb-2 mb-4 space-x-2 overflow-x-auto md:mb-0 md:w-auto whitespace-nowrap">
                    @forelse ($categories as $category)
                        <button wire:click="toggleCategory({{ $category->id }})"
                            class="@if ($categoryFilter == $category->id) focus:bg-white @endif text-navy-blue px-3 py-1 rounded-full text-sm shadow-sm border border-indigo-200 hover:bg-indigo-50 flex-shrink-0">
                            {{ $category->name }}
                        </button>
                    @empty
                        No Design Categories
                    @endforelse
                    <button wire:click="resetFilters"
                        class="flex-shrink-0 px-3 py-1 text-sm bg-white border border-indigo-200 rounded-full shadow-sm text-navy-blue hover:bg-indigo-50">
                        Reset Filters
                    </button>
                </div>


                <!-- Search -->
                <div class="relative w-full md:w-auto">
                    <input type="text" wire:model.live="searchTerm" @keypress="$wire.searchTerm = event.target.value"
                        placeholder="Search designers/exhibitions"
                        class="w-full py-2 pl-4 pr-10 text-gray-700 bg-white border border-indigo-200 rounded-full md:w-64 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <button class="absolute right-3 top-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Design Exhibits - Main scrollable content -->
            <div class="pb-24 space-y-6 sm:space-y-8">

                <div class="absolute">
                    <div x-show='$wire.modal' x-transition:enter.duration.500ms x-cloak='display:none'
                        x-transition.opacity
                        class="fixed inset-0 z-50 w-screen h-full px-5 py-16 sm:py-12 md:py-12 md:px-20 lg:py-10 bg-black/40 backdrop-blur-sm pb-26">
                        <div class="grid h-full lg:flex justify-evenly rounded-xl md:flex-row">
                            <div x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" @click.away="$wire.modal = false"
                                class="relative w-full max-w-7xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden h-[80vh] md:h-[85vh] lg:h-[90vh] flex items-center justify-center">

                                <div class="w-full h-full bg-black carousel">
                                    <!-- FRONT VIEW -->
                                    <div class="relative w-full h-full carousel-item" id="front-view">
                                        <div class="flex items-center justify-center w-full h-full">
                                            <img src="@if ($view) {{ asset('uploads/products/design-stack/' . $view->front_view) }} @endif"
                                                alt="front view" class="object-contain max-w-full max-h-full " />
                                        </div>
                                        <div
                                            class="absolute flex justify-between transform -translate-y-1/2 inset-y-1/2 left-5 right-5">
                                            <a href="#side-view"
                                                class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                            <a href="#back-view"
                                                class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                        </div>
                                    </div>

                                    <!-- BACK VIEW -->
                                    <div class="relative w-full h-full carousel-item" id="back-view">
                                        <div class="flex items-center justify-center w-full h-full">
                                            <img src="@if ($view) {{ asset('uploads/products/design-stack/' . $view->back_view) }} @endif"
                                                alt="back view" class="object-contain max-w-full max-h-full" />
                                        </div>
                                        <div
                                            class="absolute flex justify-between transform -translate-y-1/2 inset-y-1/2 left-5 right-5">
                                            <a href="#front-view"
                                                class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                            <a href="#side-view"
                                                class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                        </div>
                                    </div>

                                    <!-- SIDE VIEW -->
                                    <div class="relative w-full h-full carousel-item" id="side-view">
                                        <div class="flex items-center justify-center w-full h-full">
                                            <img src="@if ($view) {{ asset('uploads/products/design-stack/' . $view->side_view) }} @endif"
                                                alt="side view" class="object-contain max-w-full max-h-full" />
                                        </div>
                                        <div
                                            class="absolute flex justify-between transform -translate-y-1/2 inset-y-1/2 left-5 right-5">
                                            <a href="#back-view"
                                                class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                            <a href="#front-view"
                                                class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Close Button -->
                                <span class="absolute z-50 cursor-pointer top-4 right-4" wire:click='closeModal'>
                                    @svg('heroicon-o-x-mark', ['class' => 'justify btn-dark hover:bg-navy-blue btn-sm btn-circle'])
                                </span>
                            </div>


                        </div>
                    </div>

                    <div x-show='$wire.voteModal' x-transition:enter.duration.500ms x-transition:leave.duration.500ms
                        x-cloak='display:none' x-transition.opacity
                        class="fixed inset-0 z-50 w-screen h-full px-5 py-16 sm:py-12 md:py-12 md:px-20 lg:py-12 bg-black/40 backdrop-blur-sm pb-26">
                        <div x-show="$wire.voteModal" x-cloak x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            @click.away="$wire.voteModal = false"
                            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm">

                            <div
                                class="relative w-full max-w-6xl h-[90vh] bg-white rounded-xl shadow-lg overflow-hidden flex flex-col lg:flex-row">
                                @if ($view)
                                    <!-- Left: Image Section (Always Scrollable with Visible Scrollbar) -->
                                    <div
                                        class="w-full h-64 overflow-y-scroll bg-gray-100 lg:w-3/4 lg:h-full scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
                                        <div class="relative flex items-center justify-center w-full min-h-full">
                                            <img src="{{ asset('uploads/products/design-stack/' . $view->side_view) }}"
                                                alt="{{ $view->name }}"
                                                class="object-contain max-w-full max-h-full" />
                                        </div>
                                    </div>

                                    <!-- Right: Details -->
                                    <div class="w-full h-full px-6 py-5 space-y-6 overflow-y-auto bg-white lg:w-1/4">
                                        <!-- Close Button -->
                                        <div class="flex justify-end">
                                            <span wire:click='closeModal' class="cursor-pointer">
                                                @svg('heroicon-o-x-mark', ['class' => 'justify btn-dark hover:bg-navy-blue hover:text-white border-navy-blue text-black btn-sm btn-circle'])
                                            </span>
                                        </div>

                                        <!-- Design Info -->
                                        <div>
                                            <h1 class="text-xl font-semibold text-black">{{ $view->name }}</h1>
                                            <div
                                                class="inline-block px-3 py-1 mt-2 text-sm font-bold bg-gray-200 rounded-full text-navy-blue">
                                                {{ $view->category->name }}
                                            </div>
                                        </div>

                                        <!-- Vote Button -->
                                        <div class="mt-4">
                                            @if (Auth::user()->isCreative())
                                                <x-mary-button disabled label="Vote"
                                                    class="w-full bg-[#001f54] text-white hover:bg-golden hover:border-golden" />
                                            @else
                                                <x-mary-button label="Vote"
                                                    class="w-full bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                                    wire:click="castVote" spinner />
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                </div>



                @forelse ($designs as $design)
                    <div
                        class="flex flex-col overflow-hidden transition duration-200 bg-white border border-indigo-100 rounded-lg shadow-sm md:flex-row hover:shadow-md">
                        <div class="w-full h-56 md:w-1/3 sm:h-64 md:h-auto">
                            <div class="w-full h-full bg-indigo-100">
                                <img src="{{ asset('uploads/products/design-stack/' . $design->product->front_view) }}"
                                    alt="{{ $design->product->name }}"
                                    class="w-full aspect-[4/3] h-full object-cover" />
                            </div>
                        </div>
                        <div class="w-full p-4 bg-white md:w-2/3 sm:p-6">
                            <div class="flex items-start justify-between mb-2">
                                <h2 class="mb-2 text-xl font-medium text-gray-800 sm:text-2xl sm:mb-3">
                                    {{ $design->product->name }}
                                </h2>
                                <span class="px-2 py-1 text-xs bg-indigo-100 rounded-full text-navy-blue">
                                    {{ $design->category->name }}
                                </span>
                            </div>
                            <p class="mb-2 text-sm text-gray-600">Designer: <span
                                    class="font-medium">{{ $design->user->firstname . ' ' . $design->user->lastname }}</span>
                            </p>
                            <p class="mb-4 text-sm text-gray-500 sm:mb-5 sm:text-base">
                                {{ $design->product->description }}
                            </p>

                            <button wire:click='viewDesign({{ $design->product->id }})'
                                class="inline-block px-4 py-2 text-sm font-medium text-white transition duration-200 rounded cursor-pointer bg-navy-blue hover:bg-golden sm:px-6 sm:text-base">VIEW
                                EXHIBIT</button>
                            <button wire:click='vote({{ $design->product->id }})'
                                class="inline-block px-4 py-2 text-sm font-medium text-white transition duration-200 rounded cursor-pointer bg-navy-blue hover:bg-golden sm:px-6 sm:text-base">VOTE</button>
                        </div>
                    </div>
                @empty
                    <div
                        class="flex flex-col overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm md:flex-row">
                        <div class="w-full p-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto mb-4 text-indigo-200"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h2 class="mb-2 text-xl font-medium text-gray-700">No Designs Found</h2>
                            <p class="mb-4 text-gray-500">Try adjusting your search or filter criteria</p>
                            <button wire:click="resetFilters"
                                class="font-medium text-indigo-600 hover:text-indigo-800">
                                Clear all filters
                            </button>
                        </div>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if ($designs->hasPages())
                    <div class="mt-6">
                        {{ $designs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div x-transition:enter.duration.500ms x-cloak="display:none" x-show="whoRockedItBestView">

            <!-- Design Exhibits - Main scrollable content -->
            <div class="pb-24 space-y-6 sm:space-y-8">

                    <div x-show='$wire.entryModal' x-transition:enter.duration.500ms x-cloak='display:none'
                        x-transition.opacity
                        class="fixed inset-0 z-50 w-screen h-full px-5 py-16 sm:py-12 md:py-12 md:px-20 lg:py-10 bg-black/40 backdrop-blur-sm pb-26">
                        <div class="grid h-full lg:flex justify-evenly rounded-xl md:flex-row">
                            <div x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100" @click.away="$wire.modal = false"
                                class="relative w-full max-w-7xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden h-[80vh] md:h-[85vh] lg:h-[90vh] flex items-center justify-center">

                                <div class="w-full h-full bg-black carousel">



                                    <div class="relative w-full h-full carousel-item" >
                                        <div class="flex items-center justify-center w-full h-full">
                                            <img src="@if ($entry) {{ asset('uploads/contest/' . $entry->photo) }} @endif"
                                                alt="entry image" class="object-contain max-w-full max-h-full" />
                                        </div>
                                    </div>


                                </div>

                                <!-- Close Button -->
                                <span class="absolute z-50 cursor-pointer top-4 right-4" wire:click='closeEntryModal'>
                                    @svg('heroicon-o-x-mark', ['class' => 'justify btn-dark hover:bg-navy-blue btn-sm btn-circle'])
                                </span>
                            </div>


                        </div>
                    </div>

                @forelse ($entries as $entry)
                    <div
                        class="flex flex-col overflow-hidden transition duration-200 bg-white border border-indigo-100 rounded-lg shadow-sm md:flex-row hover:shadow-md">
                        <div class="w-full h-56 md:w-1/3 sm:h-64 md:h-auto">
                            <div class="w-full h-full bg-indigo-100">
                                <img src="{{ asset('uploads/contest/' . $entry->photo) }}" alt="{{ $entry->name }}"
                                    class="w-full aspect-[4/3] h-full object-cover" />
                            </div>
                        </div>
                        <div class="w-full p-4 bg-white md:w-2/3 sm:p-6">
                            <div class="flex items-start justify-between mb-2">
                                <h2 class="mb-2 text-xl font-medium text-gray-800 sm:text-2xl sm:mb-3">
                                    {{ $entry->name }}
                                </h2>

                            </div>

                            <p class="mb-4 text-sm text-gray-500 sm:mb-5 sm:text-base">
                                {{ $entry->description }}
                                {{ $entry->id }}
                            </p>



<button wire:click='viewEntry({{ $entry->id }})'
                                class="inline-block px-4 py-2 text-sm font-medium text-white transition duration-200 rounded cursor-pointer bg-navy-blue hover:bg-golden sm:px-6 sm:text-base">VIEW
                                EXHIBIT</button>
                            <button wire:click=''
                                class="inline-block px-4 py-2 text-sm font-medium text-white transition duration-200 rounded cursor-pointer bg-navy-blue hover:bg-golden sm:px-6 sm:text-base">VOTE</button>
                        </div>
                    </div>
                @empty
                    <div
                        class="flex flex-col overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm md:flex-row">
                        <div class="w-full p-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto mb-4 text-indigo-200"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h2 class="mb-2 text-xl font-medium text-gray-700">No Entries Found</h2>
                            <p class="mb-4 text-gray-500">Try adjusting your search or filter criteria</p>
                            <button wire:click="resetFilters"
                                class="font-medium text-indigo-600 hover:text-indigo-800">
                                Clear all filters
                            </button>
                        </div>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if ($entries->hasPages())
                    <div class="mt-6">
                        {{ $entries->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div x-transition:enter.duration.500ms x-cloak="display:none" x-show='whoRockedItBestEntry'
            class="max-w-4xl mx-auto mt-8 overflow-hidden bg-white border border-indigo-100 rounded-lg shadow-sm pb-36">
            <div class="px-6 py-8">
                <h2 class="mb-4 text-2xl font-semibold text-gray-800">Enter the Style Contest</h2>
                <p class="mb-6 text-gray-600">
                    Upload pictures of the outfit you rocked and stand a chance to win amazing prizes!
                </p>

                <form wire:submit.prevent="submitEntry" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" id="name" wire:model.defer="name"
                            class="block w-full text-gray-700 mt-1 border border-gray-300 rounded-md shadow-sm @error('name')
                            ring-red-500 ring-2

                            @enderror focus:ring-navy-blue focus:border-navy-blue sm:text-sm">
                        {{-- @error('name')
                            <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror --}}
                    </div>

                    <!-- Outfit Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Outfit
                            Description</label>
                        <textarea id="description" rows="3" wire:model.defer="description"
                            class="block w-full mt-1 text-gray-700 border border-gray-300 rounded-md shadow-sm @error('description')
                                ring-red-500 ring-2
                            @enderror focus:ring-navy-blue focus:border-navy-blue sm:text-sm"></textarea>
                        {{-- @error('description')
                            <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror --}}
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label for="photo" class="block mb-1 text-sm font-medium text-gray-700">Upload Your Outfit
                            Photo</label>
                        <x-mary-file omit-error="true" wire:model.defer="photo" accept="image/*">
                            <img class="object-cover w-32 h-32 border border-gray-300 rounded-md shadow-sm @error('photo')
                                ring-red-500 ring-2
                            @enderror focus:ring-navy-blue focus:border-navy-blue sm:text-sm"
                                src="{{ asset('assets/pexels-godisable-jacob-226636-794064.jpg') }}" />

                        </x-mary-file>

                        @error('photo')
                            <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <x-mary-button type="submit"
                            class="w-full py-2 font-semibold text-white rounded-md bg-navy-blue hover:bg-golden">
                            Submit Entry
                        </x-mary-button>
                    </div>
                </form>
            </div>
        </div>


    </div>
