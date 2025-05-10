<?php
use App\Models\User;
use Mary\Traits\Toast;
use App\Models\BlogPost;
use App\Models\Purchase;
use App\Models\AdminSetting;
use App\Models\BlogCategory;
use function Livewire\Volt\{state, mount, layout, uses, with, usesPagination};

usesPagination(view: 'vendor.livewire.tailwind', theme: 'simple');
layout('layouts.admin');
uses(Toast::class);

state(['searchTerm' => '']);
state(['categoryFilter' => '']);
state(['dateSort' => 'desc']);
state(['currentPage' => 1]);
state(['perPage' => 5]);
state('viewData');
state(['viewCard' => false]);
state(['categories' => fn() => BlogCategory::all()]);


with([
    'posts' => fn() => BlogPost::orderBy('created_at', $this->dateSort)
        ->where(function ($query) {
            $query->where('title', 'like', "%{$this->searchTerm}%")->orWhereHas('category', function ($category) {
                $category->where('content', 'like', "%{$this->searchTerm}%")
                ;
            });
        })->where('category', 'like' ,"%{$this->categoryFilter}%")
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
$delete = function($id){
$blogPost = BlogPost::find($id);
if(!$blogPost){
    $this->error('Post Deletion Error','Something went Wrong');
}else{
    $blogPost->delete();
    $this->success('Post Deleted Successfully');
}

}

?>

<div class="fixed w-screen h-screen pt-1 overflow-hidden bg-gray-100">

    <div wire:loading class="toast toast-top top-28 z-[9999]">
        <div class="py-3 mb-6 text-white transition-opacity duration-500 bg-green-500 border border-green-500 rounded alert-info alert top-10"
            role="alert">
            <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="ml-2">Loading...</span>
        </div>
    </div>

    <div class="fixed flex w-screen h-screen gap-1 overflow-hidden bg-gray-100">
        <!-- Sidebar component -->
        <x-admin-sidebar />
        <!-- Main content -->
        <div class="w-full px-1 pb-2 mb-16 overflow-y-scroll bg-white scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
            <header class="flex items-center justify-between w-full px-5 mb-1 bg-white">
                <h2 class="py-4 text-3xl font-extrabold text-gray-500 capitalize">
                    {{ __('Blog Posts') }}
                </h2>
                <a href="{{ route('admin.blog.post.upload') }}" wire:navigate>
                    <x-mary-button label="Upload" class="bg-navy-blue border-0" />
                </a>
            </header>

            <!-- Filters and search -->
            <div class="px-5 py-3 mb-4 bg-white rounded-lg shadow">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <!-- Search -->
                    <div class="w-full md:w-1/3">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input wire:model.live="searchTerm" @keypress="$wire.searchTerm = event.target.value"
                                id="search" type="text" placeholder="Search by title"
                                class="block w-full py-2 pl-10 pr-3 text-gray-500 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Category filter -->
                    <div class="w-full md:w-1/4">
                        <label for="status" class="sr-only">Status</label>
                        <select wire:model.live="categoryFilter" id="status"
                            @change="$wire.categoryFilter = event.target.value"
                            class="block w-full px-3 py-2 text-gray-500 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:navy-blue sm:text-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ ucfirst($category->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort control -->
                    <div class="flex justify-end w-full md:w-1/4">
                        <button wire:click="toggleSort"
                            class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span>Date: {{ ucfirst($dateSort) }}</span>
                            <svg class="w-5 h-5 ml-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </button>
                        <button wire:click="resetFilters"
                            class="px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
            <div class="px-4">
                {{ $posts->links() }}
            </div>
            <!-- blog table -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-1 py-6 sm:py-8 h-screen pb-24">

                <!-- Categories and Search - Sticky on mobile, fixed on desktop -->
                <div
                    class="bg-gray-100 p-3 sm:p-4 rounded-lg flex flex-col md:flex-row justify-between items-center mb-6 sm:mb-8 top-14 z-10">
                    <!-- Categories - Scrollable on small screens -->
                    <div class="flex space-x-2 mb-4 md:mb-0 overflow-x-auto pb-2 w-full md:w-auto whitespace-nowrap">
                        @forelse ($categories as $category)
                            <button wire:click="toggleCategory({{ $category->id }})"
                                class="@if (url()->current() == route('blog')) bg-white @endif text-gray-600 px-3 py-1 rounded-full text-sm shadow-sm border border-gray-200 hover:bg-gray-50 flex-shrink-0">
                                {{ $category->name }}
                            </button>
                        @empty
                            No Categories
                        @endforelse

                    </div>

                    <!-- Search -->
                    <div class="relative w-full md:w-auto">
                        <input type="text" wire:model.live="searchTerm"
                            @keypress="$wire.searchTerm = event.target.value" placeholder="Search creator/design"
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
                <div class="space-y-6 sm:space-y-8 pb-24 ">

                    @forelse ($posts as $post)
                        <div
                            class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                            <div class="w-full md:w-1/3 h-56 sm:h-64 md:h-auto">
                                <div class="w-full h-full bg-amber-100">
                                    <img src="{{ asset('uploads/blog/' . $post->image) }}"
                                        alt="Woman in beige outfit with cap" class="w-full h-full object-cover" />
                                </div>
                            </div>
                            <div class="w-full md:w-2/3 p-4 sm:p-6 bg-gray-50">
                                <h2 class="text-xl sm:text-2xl font-medium text-gray-700 mb-2 sm:mb-3">
                                    {{ $post->title }}</h2>
                                <p class="text-gray-500 mb-4 sm:mb-5 text-sm sm:text-base">
                                    {{ $post->content }}
                                </p>
                             <div class="flex gap-2">
                                   <a href="#"
                                    class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 sm:px-6 py-2 rounded transition duration-200 text-sm sm:text-base">READ
                                    MORE</a>
                                    <button wire:click='delete({{ $post->id }})'
                                    class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white font-medium px-4 sm:px-6 py-2 rounded transition duration-200 text-sm sm:text-base">DELETE</button>
                             </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100">
                            <div class="w-full md:w-2/3 p-4 sm:p-6 bg-gray-50">
                                <h2 class="text-xl sm:text-2xl font-medium text-gray-700 mb-2 sm:mb-3">No Post
                                    Available</h2>
                            </div>
                        </div>
                    @endforelse


                </div>
            </div>
        </div>
    </div>
</div>
