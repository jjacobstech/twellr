<?php

namespace App\Livewire;

use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\Product;
use Livewire\Component;
use App\Models\AdminSetting;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use function Livewire\Volt\{layout, with, state, uses, rules};

layout('layouts.admin');
uses(Toast::class);

state(['designs']);
state(['newDesign' => null]);
state(['newFrontView' => null]);
state(['newSideView' => null]);
state(['newBackView' => null]);
state(['designName' => '']);
state(['categories' => Category::all()]);
state(['keyword' => '']);
state(['categoryFilter' => '']);
state(['dateSort' => 'desc']);

with([
    'designs' => fn() => ($this->designs = Product::orderBy('created_at', $this->dateSort)
        ->where(function ($query) {
            $query->where('name', 'like', "%{$this->keyword}%")->orWhereHas('designer', function ($designer) {
                $designer->where('name', 'like', "%{$this->keyword}%");
            });
        })
        ->where('category_id', 'like', "%{$this->categoryFilter}%")
        ->with('designer')
        ->get()),
]);

$downloadDesign = function ($index) {
    $design = Product::find($index);
    $exists = Storage::exists('products/print-stack/' . $design->print_stack);
    if ($exists) {
       $this->success('Download Successfully.');
        return Storage::download('products/print-stack/' . $design->print_stack, $design->name);
    }

      $this->error('Product Not Found');
};

$deleteDesign = function ($id) {
    $design = Product::find($id);

    if ($design) {
        $frontViewExist = Storage::exists('products/design-stack/' . $design->front_view);
        $backViewExist = Storage::exists('products/design-stack/' . $design->back_view);
        $sideViewExist = Storage::exists('products/design-stack/' . $design->side_view);
        $printStackExist = Storage::exists('products/print-stack/' . $design->print_stack);

        if ($frontViewExist && $backViewExist && $sideViewExist && $printStackExist) {
            $frontViewDelete = Storage::delete('products/design-stack/' . $design->front_view);
            $backViewDelete = Storage::delete('products/design-stack/' . $design->back_view);
            $sideViewDelete = Storage::delete('products/design-stack/' . $design->side_view);
            $printStackDelete = Storage::delete('products/print-stack/' . $design->print_stack);
            $designDeleted = $design->delete();

            if ($frontViewDelete && $backViewDelete && $sideViewDelete && $printStackDelete && $designDeleted) {
                $this->success('Product Deleted Successfully');
            } else {
                $this->error('An error has occurred during deletion');
            }
        } else {
            $this->error('An error has occurred during deletion');
        }
    } else {
        $this->error('Product Not Found');
    }
};

$resetFilters = function () {
    $this->keyword = '';
    $this->categoryFilter = '';
};

// Method to toggle sort direction
$toggleSort = function () {
    $this->dateSort = $this->dateSort === 'desc' ? 'asc' : 'desc';

};

?>

<div class="fixed flex w-screen gap-1 pt-1 bg-gray-100">
    <div wire:loading class="toast toast-top top-28 z-[9999]">
        <div class="py-3 mb-6 text-white transition-opacity duration-500 border rounded  alert-info alert top-10 bg-navy-blue border-navy-blue"
            role="alert">
            <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
    </div>
    <x-admin-sidebar />
    <div class="w-screen h-screen p-6 pb-20 overflow-y-scroll bg-white rounded-lg shadow-lg scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
        <h2 class=" text-2xl font-bold text-gray-700 ">Uploaded Designs</h2>

            <div class="relative flex justify-evenly w-full py-2 md:py-3 gap-4">
                <input type="text" id="search-dropdown" wire:model.live="keyword"
                    class=" w-80 font-bold block p-2.5  z-20 text-sm text-gray-900 bg-gray-200 rounded-lg border-0 active:border-0 hover:border hover:border-gray-400 focus:border-0 focus:ring-0 border-navy-blue "
                    placeholder="Search by: Design/Designer" alt="Search by: Design"
                    title='Search by: Design or Designer' />

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
                    <div class="flex w-full md:w-1/2">
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

        @if (count($designs) > 0)
            <div class="grid grid-cols-1 gap-6">
                @foreach ($designs as $design)
                    <div class="overflow-hidden border rounded-lg bg-gray-50">
                        <div
                            class="flex items-center justify-between p-4 text-white border bg-navy-blue border-navy-blue">
                            <h4 class="text-lg font-medium">
                                {{ $design->designer->firstname . ' ' . $design->designer->lastname }}</h4>
                            <h4 class="text-lg font-medium">{{ $design->name }}</h4>
                            <h4 class="text-lg font-medium">
                                {{ AdminSetting::value('currency_symbol') }}{{ $design->price }}</h4>

                            <div class="text-sm font-medium"> {{ Carbon::parse($design->created_at)->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="p-4 border-0">
                            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-3">
                                <div class="flex flex-col items-center">
                                    <h5 class="mb-2 font-medium text-black">Front View</h5>
                                    <img src="{{ asset('uploads/products/design-stack/' . $design->front_view) }}"
                                        alt="Front View" class="object-contain text-black border rounded max-h-64">
                                </div>

                                <div class="flex flex-col items-center text-black">
                                    <h5 class="mb-2 font-medium">Side View</h5>
                                    <img src="{{ asset('uploads/products/design-stack/' . $design->side_view) }}"
                                        alt="Side View" class="object-contain text-black border rounded max-h-64">
                                </div>

                                <div class="flex flex-col items-center">
                                    <h5 class="mb-2 font-medium text-black">Back View</h5>
                                    <img src="{{ asset('uploads/products/design-stack/' . $design->back_view) }}"
                                        alt="Back View" class="object-contain text-black border rounded max-h-64">
                                </div>
                            </div>

                            <div class="flex justify-between mt-4">
                                <div class="text-sm text-black">
                                    <span class="font-medium text-black trunca">File:</span> <span class="truncate text-black">{{ $design->print_stack }}</span>
                                    ({{ round($design->print_stack_size / 1024, 2) }} KB)
                                </div>

                                <div class="space-x-2">
                                    <button wire:click="downloadDesign({{ $design->id }})"
                                        class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                                        Download Design
                                    </button>

                                    {{-- <button wire:click="deleteDesign({{ $design->id }})"
                                        class="px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                                        onclick="return confirm('Are you sure you want to delete this design?')">
                                        Delete
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center border rounded-lg bg-gray-50">
                <p class="text-gray-500">No designs uploaded yet.</p>
            </div>
        @endif
    </div>
</div>
