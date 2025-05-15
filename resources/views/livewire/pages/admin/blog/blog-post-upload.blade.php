<?php
use Mary\Traits\Toast;
use App\Models\BlogPost;
use App\Helpers\FileHelper;
use App\Models\BlogCategory;
use function Livewire\Volt\{state, layout, uses, usesFileUploads};

layout('layouts.admin');
uses(Toast::class);
usesFileUploads();

state(['image']);
state(['title' => '']);
state(['content' => '']);
state(['category' => '']);
state(['categories' => fn() => BlogCategory::all()]);

$uploadPost = function () {
    $post = (object) $this->validate([
        'image' => 'required|image',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'category' => 'required|string',
    ]);
    FileHelper::optimizeImage($post->image);
    $postImageData = FileHelper::getFileData($post->image);

        $postImageSave = FileHelper::saveFile($post->image, 'blog/' . $postImageData->name);

 if (!$postImageSave) {
            $this->error('Image Upload Error', 'Something went wrong, but we are working on it.');
        }

    $blogPost = BlogPost::create([
        'title' => $post->title,
        'content' => $post->content,
        'image' => $postImageData->name,
        'category_id' => $post->category,
    ]);

    if(!$blogPost){
        $this->error('Blog Upload Error', 'Something went wrong, but we are working on it.');
    }

    $this->success('Upload Successful', redirectTo: route('admin.blog.post'));

};
?>

<div class="fixed w-screen h-screen pt-1 overflow-hidden bg-gray-100">
    @error('image')
        {{ $this->error('Image is required') }}
    @enderror
    @error('title')
        {{ $this->error('Title is required') }}
    @enderror
    @error('content')
        {{ $this->error('Content is required') }}
    @enderror
    @error('category')
        {{ $this->error('Category is required') }}
    @enderror

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



    <div class="fixed flex w-screen h-screen gap-1 overflow-hidden bg-gray-100" x-data="{ remove: true }">
        <!-- Sidebar component -->
        <x-admin-sidebar />
        <!-- Main content -->
        <div class="w-full px-1 pb-2 mb-16 overflow-y-scroll bg-white scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
            <header class="flex items-center justify-between w-full px-5 mt-5">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800 mb-6 sm:mb-8">
                    {{ __('Create New Blog Post') }}

                </h2>
            </header>

            <div class=" min-h-screen p-4 sm:p-6 md:p-8">
                <div class="max-w-4xl mx-auto">
                    <form class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden"  wire:submit.prevent="uploadPost">

                        <!-- Image Upload Section -->
                        <div class="w-full bg-gray-50 p-4 sm:p-6 border-b border-gray-100">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                            <label
                                @if ($image == null) :class="remove ? ' border-2 border-gray-500 border-dashed rounded-md hover:border-navy-blue' :
                                    'border-0'" @endif
                                class="flex justify-center px-3 py-3 mt-1 ">
                                @if ($image == null)
                                    <div x-show="remove" class="space-y-1 text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-500" stroke="currentColor"
                                            fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex justify-center text-sm text-gray-600">
                                            <label for="cover-image-upload"
                                                class="relative flex justify-center font-medium text-center text-gray-500 rounded-md cursor-pointer hover:text-neutral-900 focus-within:outline-none ">
                                                <span>Upload a file</span>
                                            </label>

                                        </div>
                                        <p class="text-xs text-gray-500">
                                            JPG, PNG, GIF
                                        </p>
                                    </div>
                                @endif
                                <x-mary-file omit-error="true" @click="remove = !remove" name="image" wire:model.live="image"
                                    accept="image/png, image/jpeg, image/jpg">
                                    <img :class="remove ? '' :
                                        'border-2 border-gray-500 border-dashed rounded-md h-60 w-100 hover:border-navy-blue p-2'"
                                        src=""
                                        class="@if ($image) border-2 border-gray-500 border-dashed w-100 h-60 rounded-md hover:border-navy-blue p-2 @endif">
                                </x-mary-file>


                            </label>
                             <x-input-error :messages="$errors->get('image') ? 'Image Not Selected' : ''" class="mt-1" />
                        </div>

                        <!-- Title Field -->
                        <div class="p-4 sm:p-6 border-b border-gray-100">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Blog
                                Title</label>
                            <input type="text" id="title" name="title" wire:model="title"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 text-gray-700 focus:border-yellow-400"
                                placeholder="Enter your blog post title">
                                 <x-input-error :messages="$errors->get('title') ? 'Title Not Selected' : ''" class="mt-1" />
                        </div>

                        <!-- Content Field -->
                        <div class="p-4 sm:p-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Blog
                                Content</label>
                            <textarea id="content" name="content" wire:model="content"
                                class="text-gray-700 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 h-64 sm:h-80"
                                placeholder="Write your blog post content here..."></textarea>
                                 <x-input-error :messages="$errors->get('content') ? 'Content Cannot Be Empty' : ''" class="mt-1" />
                        </div>

                        <!-- Category Field -->
                        <div class="w-full p-4 sm:p-6">
                            <x-input-label class="mb-1 font-bold text-gray-700" for="category" :value="__('Category')" />
                            <select id="category" name="category" wire:model='category'
                                class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                <option value="">
                                    Select a category
                                </option>
                                @forelse ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ ucfirst($category->name) }}
                                    </option>
                                @empty
                                    <option value="">No category available</option>
                                @endforelse
                            </select>
                            <x-input-error :messages="$errors->get('category') ? 'Category Not Selected' : ''" class="mt-1" />
                        </div>

                        <!-- Submit Button -->
                        <div class="p-4 sm:p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button type="submit"
                                class=" bg-golden border-golden   hover:bg-navy-blue hover:border-navy-blue px-6 py-2 text-white font-medium rounded transition duration-200 flex items-center">
                                Publish Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
