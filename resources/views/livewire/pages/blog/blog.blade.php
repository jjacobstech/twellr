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
<div class=" pb-20 h-screen bg-white overflow-y-scroll scrollbar-none">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 h-screen pb-24">

        <!-- Categories and Search - Sticky on mobile, fixed on desktop -->
        <div wire:show='!view'
            class="bg-gray-100 p-3 sm:p-4 rounded-lg flex flex-col md:flex-row justify-between items-center mb-6 sm:mb-6 top-14 z-10">
            <!-- Categories - Scrollable on small screens -->
            <div class="flex space-x-2 mb-4 md:mb-0 overflow-x-auto pb-2 w-full md:w-auto whitespace-nowrap">
                @forelse ($categories as $category)
                    <button wire:click="toggleCategory({{ $category->id }})"
                        class="@if ($categoryFilter == $category->id) bg-white @endif text-gray-600 px-3 py-1 rounded-full text-sm shadow-sm border border-gray-200 hover:bg-gray-50 flex-shrink-0">
                        {{ $category->name }}
                    </button>
                @empty
                    {{-- Empty State --}}
                @endforelse
                <button wire:click="resetFilters"
                    class="@if (url()->current() == route('blog')) bg-white @endif text-gray-600 px-3 py-1 rounded-full text-sm shadow-sm border border-gray-200 hover:bg-gray-50 flex-shrink-0">
                    Reset
                </button>
            </div>

            <!-- Search -->
            <div class="relative w-full md:w-auto">
                <input type="text" wire:model.live="searchTerm" @keypress="$wire.searchTerm = event.target.value"
                    placeholder="Search creator/design"
                    class="pl-4 pr-10 py-2 bg-white text-gray-700 rounded-full border border-gray-200 w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-yellow-400">
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

        @forelse ($posts as $post)
        <div wire:show='!view' class="pb-24">
            <!-- Mobile: Single column stack -->
            <div class="block sm:hidden space-y-4 px-4">
                @foreach($posts as $post)
                    <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                        <!-- Mobile: Image on top -->
                        <div class="aspect-[16/9] bg-amber-100">
                            <img src="{{ asset('uploads/blog/' . $post->image) }}" alt="{{ $post->title }}"
                                 class="w-full h-full object-cover" />
                        </div>
                        <!-- Mobile: Content below -->
                        <div class="p-4 bg-gray-50">
                            <h2 class="text-lg font-medium text-gray-700 mb-2 line-clamp-2">
                                {{ $post->title }}
                            </h2>
                            <p class="text-gray-500 mb-3 text-sm line-clamp-2">
                                {{ $post->content }}
                            </p>
                            <button wire:click='readMore({{ $post->id }})'
                                    class="w-full bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 py-2 rounded text-sm transition duration-200">
                                READ MORE
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tablet: 2 columns, horizontal cards -->
            <div class="hidden sm:block lg:hidden px-4 sm:px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($posts as $post)
                        <div class="flex bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                            <!-- Tablet: Side image -->
                            <div class="w-32 sm:w-36 flex-shrink-0 bg-amber-100">
                                <img src="{{ asset('uploads/blog/' . $post->image) }}" alt="{{ $post->title }}"
                                     class="w-full h-full object-cover" />
                            </div>
                            <!-- Tablet: Content beside -->
                            <div class="flex-1 p-4 bg-gray-50 flex flex-col">
                                <h2 class="text-base sm:text-lg font-medium text-gray-700 mb-2 line-clamp-2">
                                    {{ $post->title }}
                                </h2>
                                <p class="text-gray-500 mb-3 text-sm line-clamp-2 flex-1">
                                    {{ $post->content }}
                                </p>
                                <button wire:click='readMore({{ $post->id }})'
                                        class="bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-3 py-2 rounded text-sm transition duration-200 self-start">
                                    READ MORE
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Desktop: 4 columns, card grid -->
            <div class="hidden lg:block px-6 xl:px-8">
                <div class="grid grid-cols-4 xl:grid-cols-6 2xl:grid-cols-8 gap-6">
                    @foreach($posts as $post)
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                            <!-- Desktop: Image on top -->
                            <div class="aspect-[4/3] bg-amber-100">
                                <img src="{{ asset('uploads/blog/' . $post->image) }}" alt="{{ $post->title }}"
                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-200" />
                            </div>
                            <!-- Desktop: Content below -->
                            <div class="p-4 bg-gray-50 h-40 flex flex-col">
                                <h2 class="text-base font-medium text-gray-700 mb-2 line-clamp-2">
                                    {{ $post->title }}
                                </h2>
                                <p class="text-gray-500 mb-3 text-sm line-clamp-3 flex-1">
                                    {{ $post->content }}
                                </p>
                                <button wire:click='readMore({{ $post->id }})'
                                        class="bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 py-2 rounded text-sm transition duration-200 self-start">
                                    READ MORE
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
            <div class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                <div class="w-full text-center p-4 sm:p-6 bg-gray-50">
                    <h2 class="text-xl sm:text-2xl font-medium text-gray-700 mb-2 sm:mb-3">No Post
                        Available</h2>
                </div>
            </div>
        @endforelse




        <!-- Blog Posts View - Individual Blog Post -->
        @if ($postView)
            <div class="min-h-screen bg-white pb-20">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <!-- Back Button -->
                    <div class="mb-6">
                        <button wire:click='close'
                            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-yellow-500 transition">
                            ← Back to Blog
                        </button>
                    </div>

                    <!-- Featured Image -->
                    <div class="flex justify-center">
                        <img src="{{ asset('uploads/blog/' . $postView->image) }}" alt="{{ $postView->title }}"
                            class=" object-contain h-64 sm:h-96 overflow-hidden rounded-lg shadow-sm mb-8" />
                    </div>

                    <!-- Post Title -->
                    <h1 class="text-2xl sm:text-4xl font-bold text-gray-800 mb-4">
                        {{ $postView->title }}
                    </h1>

                    <!-- Meta Info -->
                    <div class="text-sm text-gray-500 mb-6">
                        {{ $postView->created_at->format('F j, Y') }}
                        @if ($postCategory)
                            • {{ $postCategory->name }}
                        @endif
                    </div>

                    <!-- Post Content -->
                    <div class="prose prose-sm sm:prose lg:prose-lg max-w-none text-gray-700">
                        {!! nl2br(e($postView->content)) !!}
                    </div>
                </div>

            </div>
        @endif

    </div>


</div>
