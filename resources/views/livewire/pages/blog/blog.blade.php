<?php
use App\Models\User;
use Mary\Traits\Toast;
use App\Models\BlogPost;
use App\Models\Purchase;
use App\Models\AdminSetting;
use App\Models\BlogCategory;
use function Livewire\Volt\{state, mount, layout, uses, with, usesPagination};

usesPagination(view: 'vendor.livewire.tailwind', theme: 'simple');
layout('layouts.app');
uses(Toast::class);

state(['searchTerm' => '']);
state(['categoryFilter' => '']);
state(['dateSort' => 'desc']);
state(['currentPage' => 1]);
state(['perPage' => 5]);
state(['postView' => null]);
state(['postCategory' => '']);
state(['view' => false]);
state(['categories' => fn() => BlogCategory::all()]);

with([
    'posts' => fn() => BlogPost::orderBy('created_at', $this->dateSort)
        ->where(function ($query) {
            $query->where('title', 'like', "%{$this->searchTerm}%")->orWhereHas('category', function ($category) {
                $category->where('content', 'like', "%{$this->searchTerm}%");
            });
        })
        ->where('category_id', 'like', "%{$this->categoryFilter}%")
        ->paginate($this->perPage),
]);

// Method to reset filters

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

$readMore = function ($id) {
    $this->postView = BlogPost::where('id', '=', $id)->first();
    $this->postCategory = BlogCategory::where('id', $this->postView->category_id)->first();
    $this->view = true;
};

$close = function () {
    $this->postView = null;
    $this->view = false;
    $this->postCategory = null;
};

?>
<div class="pb-20 h-screen bg-white overflow-y-scroll scrollbar-none">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 h-screen pb-20 sm:pb-24">

        <!-- Categories and Search - Optimized for all viewports -->
        <div wire:show='!view'
            class="bg-gray-100 p-2 sm:p-3 md:p-4 rounded-lg flex flex-col md:flex-row justify-between items-center mb-4 sm:mb-6 top-14 z-10">
            <!-- Categories - Better mobile scrolling -->
            <div class="flex space-x-1 sm:space-x-2 mb-3 md:mb-0 overflow-x-auto pb-2 w-full md:w-auto whitespace-nowrap scrollbar-none">
                @forelse ($categories as $category)
                    <button wire:click="toggleCategory({{ $category->id }})"
                        class="@if ($categoryFilter == $category->id) bg-white @endif text-gray-600 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full text-xs sm:text-sm shadow-sm border border-gray-200 hover:bg-gray-50 flex-shrink-0 min-w-max">
                        {{ $category->name }}
                    </button>
                @empty
                    {{-- Empty State --}}
                @endforelse
                <button wire:click="resetFilters"
                    class="@if (url()->current() == route('blog')) bg-white @endif text-gray-600 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full text-xs sm:text-sm shadow-sm border border-gray-200 hover:bg-gray-50 flex-shrink-0 min-w-max">
                    Reset
                </button>
            </div>

            <!-- Search - Better responsive sizing -->
            <div class="relative w-full sm:w-full md:w-auto lg:w-64 xl:w-72">
                <input type="text" wire:model.live="searchTerm" @keypress="$wire.searchTerm = event.target.value"
                    placeholder="Search creator/design"
                    class="pl-3 sm:pl-4 pr-8 sm:pr-10 py-1.5 sm:py-2 bg-white text-gray-700 rounded-full border border-gray-200 w-full text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <button class="absolute right-2 sm:right-3 top-1.5 sm:top-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-yellow-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Blog Posts - Optimized grid for all viewports -->
        <div wire:show='!view' class="pb-40 sm:pb-52 md:pb-24 lg:pb-0 px-2 sm:px-4 md:px-6 lg:px-8">
            <!-- Responsive Grid Container -->
            <div class="grid grid-cols-1 xs:grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 md:gap-5 lg:gap-6">
                @forelse ($posts as $post)
                    <!-- Individual Post Card - Optimized for all screens -->
                    <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 group w-full">
                        <!-- Image - Responsive aspect ratios -->
                        <div class="aspect-[16/9] sm:aspect-[4/3] md:aspect-[16/10] lg:aspect-[4/3] bg-amber-100 overflow-hidden">
                            <img src="{{ asset('uploads/blog/' . $post->image) }}" alt="{{ $post->title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                        </div>

                        <!-- Content - Responsive padding and text -->
                        <div class="p-3 sm:p-4 bg-gray-50 flex flex-col">
                            <h2 class="text-sm sm:text-base md:text-lg font-medium text-gray-700 mb-1.5 sm:mb-2 line-clamp-2 group-hover:text-gray-900 transition-colors leading-tight">
                                {{ $post->title }}
                            </h2>
                            <p class="text-gray-500 mb-3 sm:mb-4 text-xs sm:text-sm line-clamp-2 sm:line-clamp-3 flex-1 leading-relaxed">
                                {{ $post->content }}
                            </p>
                            <button wire:click='readMore({{ $post->id }})'
                                class="w-full sm:w-auto bg-yellow-400 hover:bg-yellow-500 active:bg-yellow-600 text-white font-medium px-3 sm:px-4 py-2 sm:py-2.5 rounded-md text-xs sm:text-sm transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-yellow-300">
                                READ MORE
                            </button>
                        </div>
                    </div>

                @empty
                    <!-- Empty State - Responsive -->
                    <div class="col-span-full flex flex-col items-center justify-center py-12 sm:py-16 text-center px-4">
                        <!-- Icon -->
                        <div class="mb-4 sm:mb-6">
                            <svg class="w-16 h-16 sm:w-20 sm:h-20 mx-auto text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="bg-white rounded-xl p-6 sm:p-8 shadow-sm border border-gray-100 max-w-sm sm:max-w-md">
                            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-2 sm:mb-3">
                                No Posts Available
                            </h2>
                            <p class="text-gray-500 mb-4 sm:mb-6 leading-relaxed text-sm sm:text-base">
                                There are currently no blog posts to display. Check back soon for fresh content and updates.
                            </p>
                            <div class="space-y-2 sm:space-y-3">
                                <button onclick="window.location.reload()"
                                    class="w-full bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg text-sm transition-all duration-200 transform hover:scale-[1.02]">
                                    Refresh Page
                                </button>

                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Blog Posts View - Individual Blog Post - Responsive -->
        @if ($postView)
            <div class="min-h-screen bg-white pb-32 sm:pb-40">
                <div class="max-w-2xl sm:max-w-3xl lg:max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-5">
                    <!-- Back Button -->
                    <div class="mb-4 sm:mb-6">
                        <button wire:click='close'
                            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-yellow-500 transition">
                            ← Back to Blog
                        </button>
                    </div>

                    <!-- Featured Image - Responsive sizing -->
                    <div class="flex justify-center mb-6 sm:mb-8 rounded-lg">
                        <img src="{{ asset('uploads/blog/' . $postView->image) }}" alt="{{ $postView->title }}"
                            class="object-contain h-48 sm:h-64 md:h-80 lg:h-96 overflow-hidden rounded-lg shadow-xl" />
                    </div>

                    <!-- Post Title - Responsive text -->
                    <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800 mb-3 sm:mb-4 leading-tight">
                        {{ $postView->title }}
                    </h1>

                    <!-- Meta Info -->
                    <div class="text-xs sm:text-sm text-gray-500 mb-4 sm:mb-6">
                        {{ $postView->created_at->format('F j, Y') }}
                        @if ($postCategory)
                            • {{ $postCategory->name }}
                        @endif
                    </div>

                    <!-- Post Content - Responsive typography -->
                    <div class="prose prose-sm sm:prose md:prose-lg lg:prose-xl max-w-none text-gray-700 leading-relaxed">
                        {!! nl2br(e($postView->content)) !!}
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
