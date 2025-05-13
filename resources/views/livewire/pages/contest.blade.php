<?php
use App\Models\Vote;
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
state(['voted' => false]);
state(['entryModal' => false]);
state(['designFest' => true]);
state(['whoRockedItBest' => false]);
state([
    'name' => '',
    'description' => '',
    'photo' => null,
    'designFest' => true,
    'whoRockedItBest' => false,
    'whoRockedItBestView' => false,
    'whoRockedItBestEntry' => false,
    'votingActive' => false,
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
        ->paginate($this->perPage)
]);

mount(function () {
    if (request()->has('filter')) {
        $filter = request('filter');
        session(['filter' => $filter]);

        // Redirect to same page without the query parameter
        redirect()->route('design.contest');
    }

    if (session('filter') === 'who-rocked-it-best') {
        $this->designFest = false;
        $this->whoRockedItBest = true;
        $this->whoRockedItBestView = true;
        $this->whoRockedItBestEntry = false;
    }
    if (!request()->has('filter')) {
        session()->flash('filter');
    }
});

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

$votingInactive = function(){
$this->warning('Vote Submission Is Closed');
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
    $this->view = Product::with('designer')->find($id);
    $designer = $this->view->designer;

    if ($designer) {
        $vote = Vote::where('user_id', Auth::id())->where('contestant_id', $designer->id)->where('product_id', $this->view->id)->exists();

        $this->voted = ($vote) ? true : false;

    } else {
        $this->voted = false;
    }
    $this->voteModal = true;
};

$castVote = function () {
    $exists = Vote::where('user_id', Auth::id())
        ->where('contestant_id', $this->view->designer->id)
        ->where('product_id', $this->view->id)
        ->first();

    if (!$exists) {
        $voted = Vote::create([
            'user_id' => Auth::id(),
            'contestant_id' => $this->view->designer->id,
            'product_id' => $this->view->id,
        ]);

        if ($voted) {
            $this->success('Vote Casted');
        }

        $this->voted = true;
        $this->voteModal = false;
        $this->view = null;
    } else{
       $deleted = $exists->delete();
         if ($deleted) {
            $this->success('Vote Removed');
             $this->voted = false;
        }else {
        $this->error('Failed to remove vote');
    }
    }
};
$setTab = function () {
    $this->designFest = !$this->designFest;
    $this->whoRockedItBest = !$this->whoRockedItBest;
};

$submitEntry = function () {
    $entry = (object) $this->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'photo' => 'required|image|mimes:' . config('twellr.design_stack_format'),
    ]);

    FileHelper::optimizeImage($entry->photo);

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
            $this->photo = null;

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
    designFest: $wire.designFest,
    whoRockedItBest: $wire.whoRockedItBest,
    whoRockedItBestView: $wire.whoRockedItBestView,
    whoRockedItBestEntry: $wire.whoRockedItBestEntry,
    setTab(view) {
        if (view === 'designFest') {
            this.designFest = true;
            this.whoRockedItBest = false;
            this.whoRockedItBestView = false;
            this.whoRockedItBestEntry = false;
        }
        if (view === 'whoRockedItBest') {
            this.designFest = false;
            this.whoRockedItBest = true;
            this.whoRockedItBestView = true;
            this.whoRockedItBestEntry = false;
        }
    }
}" x-init="$dispatch('scroll-to-top');">
    <div class="min-h-screen px-3 py-4 pb-24 mx-auto max-w-7xl sm:px-6 lg:px-8 sm:py-6">
        <!-- Responsive Tabs -->
        <div class="flex flex-wrap mb-4 space-x-4 sm:space-x-6">
            <button @click="setTab('designFest')"
                class="py-3 text-sm font-bold transition-all duration-300 sm:text-md focus:outline-none"
                :class="designFest ? 'text-navy-blue border-b-2 border-navy-blue' :
                    'text-gray-500 hover:text-navy-blue border-b-2 border-transparent'">
                Design Fest
            </button>

            <button @click="setTab('whoRockedItBest')"
                class="py-3 text-sm font-bold transition-all duration-300 sm:text-md focus:outline-none"
                :class="whoRockedItBest ? 'text-navy-blue border-b-2 border-navy-blue' :
                    'text-gray-500 hover:text-navy-blue border-b-2 border-transparent'">
                Who Rocked It Best
            </button>

            <div x-transition:enter.duration.500ms x-cloak x-show="!designFest"
                class="flex flex-col w-full gap-3 sm:flex-row sm:justify-end sm:ml-auto sm:w-auto">

                <!-- Search - Full width on mobile, auto width on larger screens -->
                <div class="relative w-full sm:w-auto">
                    <input type="text" wire:model.live="search" placeholder="Search Name/Description"
                        class="w-full py-2 pl-4 pr-10 text-gray-700 bg-white border border-indigo-200 rounded-full sm:w-48 md:w-64 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <button class="absolute right-3 top-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                <!-- Upload Button - Full width on mobile -->
                <button
                    @click="whoRockedItBestEntry = !whoRockedItBestEntry, whoRockedItBestView = !whoRockedItBestView"
                    class="w-full px-3 py-1 text-sm transition-all duration-300 border rounded-full shadow-sm sm:w-auto"
                    :class="whoRockedItBestEntry ?
                        'text-white bg-navy-blue border-navy-blue hover:bg-white hover:text-navy-blue' :
                        'bg-white text-navy-blue border-indigo-200 hover:bg-indigo-50'">
                    Upload Your Outfit
                </button>
            </div>
        </div>

        <!-- Design Fest Section -->
        <div x-transition:enter.duration.500ms x-cloak x-show="designFest">
            <!-- Categories and Search - Stacked on mobile, side by side on desktop -->
            <div
                class="z-10 flex flex-col items-start justify-between p-3 mb-4 rounded-lg bg-indigo-50 sm:p-4 md:flex-row sm:mb-6">
                <!-- Categories - Scrollable horizontally -->
                <div
                    class="flex w-full pb-3 mb-3 space-x-2 overflow-x-auto md:mb-0 md:pb-0 whitespace-nowrap scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                    @forelse ($categories as $category)
                        <button wire:click="toggleCategory({{ $category->id }})"
                            class="@if ($categoryFilter == $category->id) bg-white @endif text-navy-blue px-3 py-1 rounded-full text-sm shadow-sm border border-indigo-200 hover:bg-indigo-50 flex-shrink-0">
                            {{ $category->name }}
                        </button>
                    @empty
                        <div class="px-3 py-1 text-sm text-gray-500">No Design Categories</div>
                    @endforelse
                    <button wire:click="resetFilters"
                        class="flex-shrink-0 px-3 py-1 text-sm bg-white border border-indigo-200 rounded-full shadow-sm text-navy-blue hover:bg-indigo-50">
                        Reset Filters
                    </button>
                </div>

                <!-- Search - Full width on mobile -->
                <div class="relative w-full md:w-auto">
                    <input type="text" wire:model.live="searchTerm" placeholder="Search designers/exhibitions"
                        class="w-full py-2 pl-4 pr-10 text-gray-700 bg-white border border-indigo-200 rounded-full md:w-48 lg:w-64 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <button class="absolute right-3 top-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Design Exhibits - Main content -->
            <div class="pb-16 space-y-4 sm:pb-20 sm:space-y-6">
                <!-- Modal container - Position absolute with fixed positioning when active -->
                <div class="absolute">
                    <!-- Design View Modal -->
                    <div x-show='$wire.modal' x-transition:enter.duration.500ms x-cloak x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center w-screen h-full px-2 py-8 sm:py-12 md:px-6 lg:px-20 bg-black/40 backdrop-blur-sm">
                        <div x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            @click.away="$wire.modal = false"
                            class="relative w-full max-w-7xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden h-[85vh] sm:h-[85vh] md:h-[90vh] flex items-center justify-center">

                            <div class="w-full h-full bg-black carousel">
                                <!-- FRONT VIEW -->
                                <div class="relative w-full h-full carousel-item" id="front-view">
                                    <div class="flex items-center justify-center w-full h-full">
                                        <img loading="lazy" src="@if ($view) {{ asset('uploads/products/design-stack/' . $view->front_view) }} @endif"
                                            alt="front view" class="object-contain max-w-full max-h-full" />
                                    </div>
                                    <div
                                        class="absolute flex justify-between transform -translate-y-1/2 inset-y-1/2 left-2 right-2 sm:left-5 sm:right-5">
                                        <a href="#side-view"
                                            class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                        <a href="#back-view"
                                            class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                    </div>
                                </div>

                                <!-- BACK VIEW -->
                                <div class="relative w-full h-full carousel-item" id="back-view">
                                    <div class="flex items-center justify-center w-full h-full">
                                        <img loading="lazy" src="@if ($view) {{ asset('uploads/products/design-stack/' . $view->back_view) }} @endif"
                                            alt="back view" class="object-contain max-w-full max-h-full" />
                                    </div>
                                    <div
                                        class="absolute flex justify-between transform -translate-y-1/2 inset-y-1/2 left-2 right-2 sm:left-5 sm:right-5">
                                        <a href="#front-view"
                                            class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                        <a href="#side-view"
                                            class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                    </div>
                                </div>

                                <!-- SIDE VIEW -->
                                <div class="relative w-full h-full carousel-item" id="side-view">
                                    <div class="flex items-center justify-center w-full h-full">
                                        <img loading="lazy" src="@if ($view) {{ asset('uploads/products/design-stack/' . $view->side_view) }} @endif"
                                            alt="side view" class="object-contain max-w-full max-h-full" />
                                    </div>
                                    <div
                                        class="absolute flex justify-between transform -translate-y-1/2 inset-y-1/2 left-2 right-2 sm:left-5 sm:right-5">
                                        <a href="#back-view"
                                            class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                        <a href="#front-view"
                                            class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Close Button - Better positioning for all screens -->
                            <span class="absolute z-50 cursor-pointer top-2 right-2 sm:top-4 sm:right-4"
                                wire:click='closeModal'>
                                @svg('heroicon-o-x-mark', ['class' => 'justify btn-dark hover:bg-navy-blue btn-sm btn-circle'])
                            </span>
                        </div>
                    </div>

                    <!-- Vote Modal -->
                    <div x-show='$wire.voteModal' x-transition:enter.duration.500ms x-transition:leave.duration.500ms
                        x-cloak x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center w-screen h-full px-2 py-8 sm:py-12 md:px-6 lg:px-20 bg-black/40 backdrop-blur-sm">
                        <div x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            @click.away="$wire.voteModal = false"
                            class="relative w-full max-w-6xl h-[90vh]  rounded-xl shadow-lg mt-40 sm:mt-0 overflow-hidden flex flex-col lg:flex-row">
                            @if ($view)
                                <!-- Left: Image Section -->
                                <div
                                    class="w-full h-64 overflow-y-scroll bg-gray-100 sm:h-80 scrollbar-none lg:h-full lg:w-3/4">
                                    <div class="scrollbar-none relative flex items-center justify-center w-full bg-black min-h-full ">
                                        <img loading="lazy" src="{{ asset('uploads/products/design-stack/' . $view->side_view) }}"
                                            alt="{{ $view->name }}" class="object-cover max-w-full scrollbar-none max-h-full" />
                                    </div>
                                </div>

                                <!-- Right: Details -->
                                <div
                                    class="w-full h-64 px-4 py-4 rounded-b-lg lg:rounded-none space-y-4 overflow-y-auto bg-white sm:h-auto lg:h-full sm:px-6 sm:py-5 sm:space-y-6 lg:w-1/4">
                                    <!-- Close Button -->
                                    <div class="flex justify-end">
                                        <span wire:click='closeModal' class="cursor-pointer">
                                            @svg('heroicon-o-x-mark', ['class' => 'justify btn-dark hover:bg-navy-blue hover:text-white border-navy-blue text-black btn-sm btn-circle'])
                                        </span>
                                    </div>

                                    <!-- Design Info -->
                                    <div>
                                        <h1 class="text-lg font-semibold text-black sm:text-xl">{{ $view->name }}
                                        </h1>
                                        <div
                                            class="inline-block px-3 py-1 mt-2 text-sm font-bold bg-gray-200 rounded-full text-navy-blue">
                                            {{ $view->category->name }}
                                        </div>
                                    </div>

                                    <!-- Vote Button -->
                                    <div class="mt-3 sm:mt-4">

                                       @if ($voted)
                                             <x-mary-button label="Voted"
                                                    class="w-full bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                                    wire:click="castVote" spinner />
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

                <!-- Design Cards - Improved responsiveness -->
                @forelse ($designs as $design)
                    <div
                        class="flex flex-col overflow-hidden transition duration-200 bg-white border border-indigo-100 rounded-lg shadow-sm md:flex-row hover:shadow-md">
                        <!-- Image container - Fixed height on mobile, auto on desktop -->
                        <div class="w-full h-48 sm:h-56 md:w-1/3 md:h-auto">
                            <div class="w-full h-full bg-indigo-100">
                                <img loading="lazy" src="{{ asset('uploads/products/design-stack/' . $design->product->front_view) }}"
                                    alt="{{ $design->product->name }}" class="object-cover w-full h-full aspect-[4/2]" />
                            </div>
                        </div>
                        <!-- Content container - Better spacing -->
                        <div class="w-full p-3 bg-white sm:p-4 md:p-5 md:w-2/3">
                            <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                <h2 class="text-lg font-medium text-gray-800 sm:text-xl md:text-2xl">
                                    {{ $design->product->name }}
                                </h2>
                                <span class="px-2 py-1 text-xs bg-indigo-100 rounded-full text-navy-blue">
                                    {{ $design->category->name }}
                                </span>
                            </div>
                            <p class="mb-1 text-sm text-gray-600 sm:mb-2">Designer: <span
                                    class="font-medium">{{ $design->user->firstname . ' ' . $design->user->lastname }}</span>
                            </p>
                            <p class="mb-3 text-sm text-gray-500 sm:mb-4 line-clamp-3 sm:line-clamp-2 ">
                                {{ $design->product->description }}
                            </p>

                            <!-- Action buttons - Better spacing and touch targets -->
                            <div class="flex flex-wrap gap-2 sm:gap-3">
                                <button wire:click='viewDesign({{ $design->product->id }})'
                                    class="px-3 py-2 text-sm font-medium text-white transition duration-200 rounded cursor-pointer sm:px-4 bg-navy-blue hover:bg-golden">VIEW
                                    EXHIBIT</button>
                                @if (!$design->user->id !=  Auth::id())

                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Empty state - Consistent padding -->
                    <div
                        class="flex flex-col overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm md:flex-row">
                        <div class="w-full p-6 text-center sm:p-8">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-12 h-12 mx-auto mb-3 text-indigo-200 sm:w-16 sm:h-16 sm:mb-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h2 class="mb-2 text-lg font-medium text-gray-700 sm:text-xl">No Designs Found</h2>
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
                    <div class="mt-4 sm:mt-6">
                        {{ $designs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Who Rocked It Best View -->
        <div x-transition:enter.duration.500ms x-cloak x-show="whoRockedItBestView">
            <!-- Entries content -->
            <div class="pb-16 space-y-4 sm:pb-20 sm:space-y-6">
                <!-- Entry Modal -->
                <div x-show='$wire.entryModal' x-transition:enter.duration.500ms x-cloak x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center w-screen h-full px-2 py-8 sm:py-12 md:px-6 lg:px-20 bg-black/40 backdrop-blur-sm">
                    <div x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        @click.away="$wire.entryModal = false"
                        class="relative w-full max-w-7xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden h-[85vh] sm:h-[85vh] md:h-[90vh] flex items-center justify-center">

                        <div class="w-full h-full bg-black carousel">
                            <div class="relative w-full h-full carousel-item">
                                <div class="flex items-center justify-center w-full h-full">
                                    <img loading="lazy" src="@if ($entry) {{ asset('uploads/contest/' . $entry->photo) }} @endif"
                                        alt="entry image" class="object-contain max-w-full max-h-full" />
                                </div>
                            </div>
                        </div>

                        <!-- Close Button -->
                        <span class="absolute z-50 cursor-pointer top-2 right-2 sm:top-4 sm:right-4"
                            wire:click='closeEntryModal'>
                            @svg('heroicon-o-x-mark', ['class' => 'justify btn-dark hover:bg-navy-blue btn-sm btn-circle'])
                        </span>
                    </div>
                </div>

                <!-- Entry Cards -->
                @forelse ($entries as $entry)
                    <div
                        class="flex flex-col overflow-hidden transition duration-200 bg-white border border-indigo-100 rounded-lg shadow-sm md:flex-row hover:shadow-md">
                        <!-- Image container - Fixed height on mobile, auto on desktop -->
                        <div class="w-full h-48 sm:h-56 md:w-1/3 md:h-auto">
                            <div class="w-full h-full bg-indigo-100">
                                <img loading="lazy" src="{{ asset('uploads/contest/' . $entry->photo) }}" alt="{{ $entry->name }}"
                                    class="object-cover w-full h-full" />
                            </div>
                        </div>
                        <!-- Content container -->
                        <div class="w-full p-3 bg-white sm:p-4 md:p-5 md:w-2/3">
                            <div class="flex items-start justify-between mb-2">
                                <h2 class="text-lg font-medium text-gray-800 sm:text-xl md:text-2xl">
                                    {{ $entry->name }}
                                </h2>
                            </div>

                            <p class="mb-3 text-sm text-gray-500 sm:mb-4 line-clamp-3 sm:line-clamp-none">
                                {{ $entry->description }}
                            </p>

                            <!-- Action buttons -->
                            <div class="flex flex-wrap gap-2 sm:gap-3">
                                <button wire:click='viewEntry({{ $entry->id }})'
                                    class="px-3 py-2 text-sm font-medium text-white transition duration-200 rounded cursor-pointer sm:px-4 bg-navy-blue hover:bg-golden">VIEW
                                    EXHIBIT</button>
                                <button wire:click=''
                                    class="px-3 py-2 text-sm font-medium text-white transition duration-200 rounded cursor-pointer sm:px-4 bg-navy-blue hover:bg-golden">VOTE</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Empty state -->
                    <div
                        class="flex flex-col overflow-hidden bg-white border border-gray-100 rounded-lg shadow-sm md:flex-row">
                        <div class="w-full p-6 text-center sm:p-8">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-12 h-12 mx-auto mb-3 text-indigo-200 sm:w-16 sm:h-16 sm:mb-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h2 class="mb-2 text-lg font-medium text-gray-700 sm:text-xl">No Entries Found</h2>
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
                    <div class="mt-4 sm:mt-6">
                        {{ $entries->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Entry Form -->
        <div x-transition:enter.duration.500ms x-cloak x-show='whoRockedItBestEntry'
            class="max-w-4xl pb-20 mx-auto mt-4 overflow-hidden bg-white border border-indigo-100 rounded-lg shadow-sm sm:mt-6 sm:pb-24">
            <div class="px-4 py-6 sm:px-6 sm:py-8">
                <h2 class="mb-3 text-xl font-semibold text-gray-800 sm:text-2xl">Enter the Style Contest</h2>
                <p class="mb-4 text-gray-600 sm:mb-6">
                    Upload pictures of the outfit you rocked and stand a chance to win amazing prizes!
                </p>

                <form wire:submit.prevent="submitEntry" class="space-y-4 sm:space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" id="name" wire:model.defer="name"
                            class="block w-full mt-1 text-gray-700 border border-gray-300 rounded-md shadow-sm focus:ring-navy-blue focus:border-navy-blue sm:text-sm
                            @error('name') ring-red-500 ring-2 @enderror">
                    </div>

                    <!-- Outfit Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Outfit
                            Description</label>
                        <textarea id="description" rows="3" wire:model.defer="description"
                            class="block w-full mt-1 text-gray-700 border border-gray-300 rounded-md shadow-sm focus:ring-navy-blue focus:border-navy-blue sm:text-sm
                            @error('description') ring-red-500 ring-2 @enderror"></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label for="photo" class="block mb-1 text-sm font-medium text-gray-700">Upload Your Outfit
                            Photo</label>
                        <x-mary-file omit-error="true" wire:model.defer="photo" accept="image/*">
                            <img loading="lazy" class="object-cover w-24 h-24 sm:w-32 sm:h-32 border border-gray-300 rounded-md shadow-sm
                                @error('photo') ring-red-500 ring-2 @enderror"
                                src="{{ asset('assets/pexels-godisable-jacob-226636-794064.jpg') ??  $photo }}" />
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
</div>
