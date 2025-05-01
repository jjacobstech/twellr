<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Product;
use Livewire\Component;
use App\Models\AdminSetting;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{layout, mount, state, rules};

layout('layouts.admin');

state(['designs']);
state(['newDesign' => null]);
state(['newFrontView' => null]);
state(['newSideView' => null]);
state(['newBackView' => null]);
state(['designName' => '']);
state(['showSuccessMessage' => false]);
state(['successMessage' => '']);


rules([
    'newDesign' => 'required|file|max:10240', // Max 10MB
    'newFrontView' => 'required|image|max:5120', // Max 5MB
    'newSideView' => 'required|image|max:5120',
    'newBackView' => 'required|image|max:5120',
    'designName' => 'required|string|max:255',
]);
mount(fn() => $this->loadDesigns());

$loadDesigns = function () {
    $this->designs = Product::with('designer')->get();
};

$search = function ($keyword) {
    $this->designs = Product::where('name' ,'like',"%$keyword%")
    ->take(5)
    ->get();
};

$downloadDesign = function ($index) {

    $design = Product::find($index);
    $exists = Storage::exists('products/print-stack/'.$design->print_stack);
    if ($exists) {

        $this->showSuccessMessage = true;
    $this->successMessage = 'Download Successfully.';
    $this->hideSuccessMessage();
        return Storage::download('products/print-stack/'.$design->print_stack, $design->name);

    }

    session()->flash('error', 'Design file not found!');
};
$hideSuccessMessage = function () {
    // Auto-hide the success message after 3 seconds
    $this->dispatch(
        'setTimeout',"
        document.querySelector('#success-alert').classList.add('opacity-0');
        setTimeout(() => {
            $this->showSuccessMessage = false;
        }, 500);
    ",
        ms: 3000,
    );
};

?>

<div class="flex pt-1 gap-1 fixed w-screen bg-gray-100">
        @if ($showSuccessMessage)
        <div id="success-alert" class="toast toast-top top-16 z-[9999]">
            <div class=" alert-info alert top-10 bg-navy-blue border border-navy-blue text-white px-4 py-3 rounded mb-6 transition-opacity duration-500 "
                role="alert">
                <span class="block sm:inline ">{{ $successMessage }}</span>
            </div>
        </div>
    @endif

    <div wire:loading class="toast toast-top top-28 z-[9999]">
        <div class=" alert-info alert top-10 bg-navy-blue border border-navy-blue text-white py-3 rounded mb-6 transition-opacity duration-500 "
            role="alert">
            <svg class="animate-spin inline-block bw-spinner h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
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
    <div class="p-6 bg-white rounded-lg shadow-lg  h-screen pb-20 w-screen overflow-y-scroll">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Design Viewer</h2>

        <!-- Designs List -->
        <div class="flex justify-between w-100">
            <h3 class="text-xl font-semibold mb-4 text-gray-600 w-1/2 py-3">Uploaded Designs</h3>
            <div class="relative flex w-full py-2 md:py-3 justify-end">
                <input type="text" id="search-dropdown" x-model="term" x-on:keydown="$wire.search(term)"
                    x-on:keyup="$wire.search(term)"
                    class=" w-80 font-bold block p-2.5  z-20 text-sm text-gray-900 bg-gray-200 rounded-lg border-0 active:border-0 hover:border hover:border-gray-400 focus:border-0 focus:ring-0 border-navy-blue "
                    placeholder="Search by: Design" alt="Search by: Design" title='Search by: Design' />



            </div>
        </div>

        @if (count($designs) > 0)
            <div class="grid grid-cols-1 gap-6">
                @foreach ($designs as $design)
                    <div class="border rounded-lg overflow-hidden bg-gray-50">
                        <div class="p-4 bg-navy-blue border border-navy-blue flex justify-between items-center text-white">
                              <h4 class="text-lg font-medium">{{ $design->designer->firstname. " " .$design->designer->lastname }}</h4>
                            <h4 class="text-lg font-medium">{{ $design->name }}</h4>
                            <h4 class="text-lg font-medium">{{ AdminSetting::value('currency_symbol') }}{{ $design->price }}</h4>

                          <div class="text-sm font-medium">  {{ Carbon::parse($design->created_at)->format('d/m/Y') }}</div>
                        </div>

                        <div class="p-4 border-0">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="flex flex-col items-center">
                                    <h5 class="font-medium mb-2 text-black">Front View</h5>
                                    <img src="{{ asset('uploads/products/design-stack/' . $design->front_view) }}"
                                        alt="Front View" class="max-h-64 object-contain border rounded text-black">
                                </div>

                                <div class="flex flex-col items-center text-black">
                                    <h5 class="font-medium mb-2">Side View</h5>
                                    <img src="{{ asset('uploads/products/design-stack/' . $design->side_view) }}"
                                        alt="Side View" class="max-h-64 object-contain border rounded text-black">
                                </div>

                                <div class="flex flex-col items-center">
                                    <h5 class="font-medium mb-2 text-black">Back View</h5>
                                    <img src="{{ asset('uploads/products/design-stack/' . $design->back_view) }}"
                                        alt="Back View" class="max-h-64 object-contain border rounded text-black">
                                </div>
                            </div>

                            <div class="flex justify-between mt-4">
                                <div class="text-sm text-black">
                                    <span class="font-medium text-black">File:</span> {{ $design->print_stack }}
                                    ({{ round($design->print_stack_size / 1024, 2) }} KB)
                                </div>

                                <div class="space-x-2">
                                    <button wire:click="downloadDesign({{ $design->id }})"
                                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                        Download Design
                                    </button>

                                    <button wire:click="deleteDesign({{ $design->id }})"
                                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                        onclick="return confirm('Are you sure you want to delete this design?')">
                                        Delete
                                    </button>
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
