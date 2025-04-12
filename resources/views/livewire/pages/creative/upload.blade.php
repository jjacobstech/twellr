<?php

use App\Models\Product;
use App\Models\Category;
use App\Helpers\FileHelper;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
   // use WithFileUploads;
    public string $name = '';
    public string $price = '';
    public string $category = '';
    public string $description = '';
    public $designUpload;
    public $frontView;
    public $backView;
    public $sideView;
    public $printUpload;
    public $categories;
    public bool $spinner = false;

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function uploadProduct()
    {
        $product = (object) ($validated = $this->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'description' => 'required|string',
            'frontView' => ['required', 'file', 'mimes:' . config('twellr.design_stack_format')],
            'backView' => ['required', 'file', 'mimes:' . config('twellr.design_stack_format')],
            'sideView' => ['required', 'file', 'mimes:' . config('twellr.design_stack_format')],
            'printUpload' => ['required', 'file', 'mimes:' . config('twellr.printable_stack_format')],
        ]));

        $frontView = $product->frontView;
        $backView = $product->backView;
        $sideView = $product->sideView;
        $printFile = $product->printUpload;

        //File Data Fetch
        $frontViewData = FileHelper::getFileData($frontView);
        $backViewData = FileHelper::getFileData($backView);
        $sideViewData = FileHelper::getFileData($sideView);
        $printFileData = FileHelper::getFileData($printFile);

        // Save Files Into Storage
        $frontViewSave = FileHelper::saveFile($frontView, 'products/design-stack/' . $frontViewData->name);
        $backViewSave = FileHelper::saveFile($backView, 'products/design-stack/' . $backViewData->name);
        $sideViewSave = FileHelper::saveFile($sideView, 'products/design-stack/' . $sideViewData->name);
        $printFileSave = FileHelper::saveFile($printFile, 'products/print-stack/' . $printFileData->name);

        if (!$frontViewSave || !$backViewSave || !$sideViewSave || !$printFileSave) {
            abort(500, 'Something went wrong, but we are working on it.');
        }

        $productSaved = Product::create([
            'user_id' => auth()->user()->id,
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
            'description' => $product->description,
            'front_view' => $frontViewData->name,
            'front_view_mime' => $frontViewData->mime,
            'front_view_extension' => $frontViewData->extension,
            'front_view_size' => $frontViewData->size,
            'back_view' => $backViewData->name,
            'back_view_mime' => $backViewData->mime,
            'back_view_extension' => $backViewData->extension,
            'back_view_size' => $backViewData->size,
            'side_view' => $sideViewData->name,
            'side_view_mime' => $sideViewData->mime,
            'side_view_extension' => $sideViewData->extension,
            'side_view_size' => $sideViewData->size,
            'print_stack' => $printFileData->name,
            'print_stack_mime' => $printFileData->mime,
            'print_stack_extension' => $printFileData->extension,
            'print_stack_size' => $printFileData->size,
            'status' => 'available',
        ]);

        if (!$productSaved) {
            abort(500);
        }

        $this->cleanupOldUploads();

        session()->flash('status', 'true');
        $this->redirectIntended(route('creative.upload'), true);
    }
}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,
}"
    x-on:livewire-upload-finish="showNotification('Upload Complete', 'File Upload Successful', 'success', 5)">

    @if (session('status'))
        @script
            <script>
                showNotification('Product Added', 'Product Added Successful', 'success', 5)
            </script>
        @endscript
    @endif

    {{-- Display errors for file uploads --}}
    @if (
        $errors->get('designUpload') ||
            $errors->get('printUpload') ||
            $errors->get('frontView') ||
            $errors->get('backView') ||
            $errors->get('sideView'))
        @script
            <script>
                showNotification('Invalid File Format', "Check Uploads", 'error', 5)
            </script>
        @endscript
    @endif

    {{-- Main Layout: Sidebar + Content --}}
    <div class="flex flex-col md:flex-row md:gap-1">
        {{-- Sidebar Placeholder - Ensure this component is also responsive --}}
        <x-creative-sidebar />

        {{-- Main Content Area --}}
        <div class="w-full p-4 text-xl bg-white">
            {{-- Background Image Container - Let height be determined by content or use min-h for better flexibility --}}
            <div style="background-image: url('{{ asset('assets/blurred.png') }}')"
                class="relative grid justify-center text-white bg-no-repeat bg-cover rounded-lg min-h-[calc(100vh-2rem)] md:min-h-full">

                {{-- Back Button - Positioned absolutely within the background container --}}
                <div class="absolute z-10 top-4 left-4">
                    <img x-show="backButton" x-transition:enter.duration.500ms x-cloak
                        @click="form = true, uploadModal = false, backButton = false"
                        class="w-12 h-12 transition cursor-pointer md:w-16 md:h-16 hover:scale-110"
                        src="{{ asset('assets/back-arrow.png') }}" alt="Back">
                </div>

                {{-- Form Container --}}
                <form x-transition:enter.duration.500ms x-cloak="display:none"
                    :class="uploadModal ? 'w-full p-4 md:p-8 lg:p-12' : 'w-full max-w-4xl bg-[#dedddb] rounded-[40px] my-6'"
                    wire:submit="uploadProduct" enctype="multipart/form-data">
                    @csrf

                    {{-- Main Form View --}}
                    <div x-show="form" x-transition:enter.duration.700ms class="py-3">
                        <div class="flex flex-col px-8 py-5">

                            {{-- Name and Price Row - Stack vertically on small, horizontal on medium+ --}}
                            <div class="flex flex-col md:flex-row md:gap-6">
                                <div class="flex-1 w-full md:w-3/5">
                                    <x-input-label :value="__('Name')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="name" />
                                    <x-text-input id="name" :class="$errors->get('name')
                                        ? 'block w-full mt-2 border-1 border-red-600 bg-[#bebebe] rounded-xl'
                                        : 'block border-0 w-full mt-2 bg-[#bebebe] rounded-xl'" type="text" name="name"
                                        wire:model.live="name" required autofocus autocomplete="name" />
                                </div>

                                <div class="w-full md:w-2/5">
                                    <x-input-label :value="__('Price')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="price" />
                                    <x-text-input id="price" :class="$errors->get('price')
                                        ? 'block w-full mt-2 border-1 border-red-600 bg-[#bebebe] rounded-xl'
                                        : 'block border-0 w-full mt-2 bg-[#bebebe] rounded-xl'" wire:model.live="price" type="text"
                                        name="price" required autofocus autocomplete="price" />
                                </div>
                            </div>

                            {{-- Category --}}
                            <div class="mt-4">
                                <x-input-label :value="__('Category')" class="text-gray-500 font-extrabold text-[17px]"
                                    for="category" />
                                <x-select id="category" wire:model.live="category" :class="$errors->get('category')
                                    ? 'block w-full mt-2 border-red-600 bg-[#bebebe] border-1 rounded-xl'
                                    : 'block w-full mt-2 border-[#bebebe] bg-[#bebebe] border-0 rounded-xl'" type="text"
                                    name="category" required autofocus autocomplete="category">
                                    <x-slot name="options">
                                        <option class="text-black" disabled value="">Select a category</option>
                                        @foreach ($categories as $category)
                                            <option class="text-black" id="category{{ $category->id }}"
                                                name="{{ $category->name }}" value="{{ $category->name }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </x-slot>
                                </x-select>
                            </div>

                            {{-- Description and Uploads Trigger Row - Stack vertically on small, horizontal on large+ --}}
                            <div class="flex flex-col mt-4 lg:flex-row lg:gap-6">
                                <div class="w-full lg:w-3/5">
                                    <x-input-label :value="__('Description')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="description" />
                                    {{-- Use standard textarea or ensure x-textarea-input handles responsiveness --}}
                                    <x-textarea-input wire:model.live="description" :class="$errors->get('description')
                                        ? 'w-full border-1 border-red-600 px-2 py-1 mt-2 bg-[#bebebe] rounded-xl text-black h-28'
                                        : 'w-full px-2 py-1 mt-2 bg-[#bebebe] border-0 rounded-xl text-black h-28'"
                                        id="description"></x-textarea-input>
                                </div>

                                <div class="flex flex-col items-center w-full mt-4 lg:w-2/5 lg:mt-0 lg:items-end">
                                    <x-input-label :value="__('Uploads')"
                                        class="text-gray-500 font-extrabold text-[17px] mb-2 text-center lg:text-left"
                                        for="uploads" />
                                    <img @click="form=false, uploadModal=true, backButton=true"
                                        class="h-24 w-24 md:h-28 md:w-28 hover:cursor-pointer rounded-2xl {{ $errors->has('frontView') ||
                                        $errors->has('backView') ||
                                        $errors->has('sideView') ||
                                        $errors->has('printUpload')
                                            ? 'border-2 border-red-600'
                                            : '' }}"
                                        src="{{ asset('assets/image.png') }}" alt="Upload Trigger">
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t-2 border-[#bebebe] w-full"></div>

                        {{-- Submit Button --}}
                        <div class="flex justify-center py-4">
                            <x-mary-button label="MONETIZE DESIGN"
                                class="btn hover:bg-navy-blue hover:text-white active:bg-navy-blue bg-[#f7aa10] text-neutral-900 px-6 py-4 rounded-xl font-bold uppercase text-[13px] focus:ring-0 focus:bg-navy-blue focus:text-white border-0"
                                type="submit" spinner />
                        </div>
                    </div>
                    {{-- Form End --}}

                    {{-- Upload Modal --}}
                    <div x-show="uploadModal" x-transition:enter.duration.500ms x-cloak class="py-10 md:py-16">
                        {{-- Grid layout for uploads - 1 col default, 2 cols on small+, 4 cols on large+ --}}
                        <div
                            class="grid grid-cols-1 gap-6 px-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-8 md:px-8 lg:px-16">

                            <div class="grid content-start justify-center space-y-2 text-center">
                                <label>
                                    <x-mary-file omit-error="true" class="grid items-center"
                                        change-text="Upload Front View" wire:model.live="frontView"
                                        accept="image/png, image/jpeg, image/jpg">
                                        {{-- Responsive Image Container --}}
                                        <div class="relative w-32 h-32 mx-auto group md:w-36 md:h-36 lg:w-40 lg:h-40">

                                            <div
                                                class="relative z-40 grid items-center justify-center w-full h-full transition-all duration-500 bg-white cursor-pointer group-hover:scale-105 group-hover:shadow-xl rounded-xl">
                                                {{-- Preview or Placeholder --}}

                                                <img class="object-cover w-full h-full rounded-xl"
                                                    src="{{ asset('assets/uploadDesignStack.png') }}"
                                                    alt="Upload Front View">
                                            </div>
                                            {{-- Dashed border overlay on hover --}}
                                            <div
                                                class="absolute inset-0 z-30 flex items-center justify-center w-full h-full transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                            </div>
                                        </div>
                                    </x-mary-file>
                                    @error('frontView')
                                        <span class="block text-xs text-red-600">Invalid File Format</span>
                                    @enderror
                                </label>
                                <p class="text-sm">
                                    <span>Front View</span>
                                    <span class="block text-xs text-gray-100">jpeg, png, jpg only</span>


                                </p>
                            </div>

                            {{-- Back View --}}
                            <div class="grid content-start justify-center space-y-2 text-center">
                                <label>
                                    <x-mary-file omit-error="true" change-text="Upload Back View" wire:model.live="backView"
                                        accept="image/png, image/jpeg, image/jpg">
                                        <div class="relative w-32 h-32 mx-auto group md:w-36 md:h-36 lg:w-40 lg:h-40">
                                            <div
                                                class="relative z-40 grid items-center justify-center w-full h-full transition-all duration-500 bg-white cursor-pointer group-hover:scale-105 group-hover:shadow-xl rounded-xl">

                                                <img class="object-cover w-full h-full rounded-xl"
                                                    src="{{ asset('assets/uploadDesignStack.png') }}"
                                                    alt="Upload Back View">

                                            </div>
                                            <div
                                                class="absolute inset-0 z-30 flex items-center justify-center w-full h-full transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                            </div>
                                        </div>
                                    </x-mary-file>
                                    @error('backView')
                                        <span class="block text-xs text-red-600">Invalid File Format</span>
                                    @enderror
                                </label>
                                <p class="text-sm">
                                    <span>Back View</span>
                                    <span class="block text-xs text-gray-100">jpeg, png, jpg only</span>

                                </p>
                            </div>

                            {{-- Side View --}}
                            <div class="grid content-start justify-center space-y-2 text-center">
                                <label>
                                    <x-mary-file omit-error="true" change-text="Upload Side View"
                                        wire:model="sideView" accept="image/png, image/jpeg, image/jpg">
                                        <div class="relative w-32 h-32 mx-auto group md:w-36 md:h-36 lg:w-40 lg:h-40">
                                            <div
                                                class="relative z-40 grid items-center justify-center w-full h-full transition-all duration-500 bg-white cursor-pointer group-hover:scale-105 group-hover:shadow-xl rounded-xl">
                                                <img class="object-cover w-full h-full rounded-xl"
                                                    src="{{ asset('assets/uploadDesignStack.png') }}"
                                                    alt="Upload Side View">
                                            </div>
                                            <div
                                                class="absolute inset-0 z-30 flex items-center justify-center w-full h-full transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                            </div>
                                        </div>
                                    </x-mary-file>
                                    @error('sideView')
                                        <span class="block text-xs text-red-600">Invalid File Format</span>
                                    @enderror
                                </label>
                                <p class="text-sm">
                                    <span>Side View</span>
                                    <span class="block text-xs text-gray-100">jpeg, png, jpg only</span>

                                </p>
                            </div>

                            {{-- Printable Stack --}}
                            <div class="grid content-start justify-center space-y-2 text-center">
                                <label
                                    title="Upload Print file ({{ config('twellr.printable_stack_format', 'Allowed Formats') }})">
                                    <input class="hidden" type="file" accept="*" wire:model.live="printUpload"
                                        name="printable_stack" id="printable_stack">

                                    <div class="relative w-32 h-32 mx-auto group md:w-36 md:h-36 lg:w-40 lg:h-40">
                                        <div class="relative z-40 flex items-center justify-center w-full h-full p-2 transition-all duration-500 bg-white cursor-pointer group-hover:scale-105 group-hover:shadow-xl rounded-xl "
                                            id="printable-container">
                                            {{-- Display file icon or placeholder --}}
                                            <img class="object-contain w-full h-full"
                                                src="{{ $printUpload ? asset('assets/file.png') : asset('assets/uploadPrintableStack.png') }}"
                                                id="printImage" alt="Printable Stack Upload">
                                        </div>
                                        <div
                                            class="absolute inset-0 z-30 flex items-center justify-center w-full h-full transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                        </div>
                                    </div>
                                    @error('printUpload')
                                        <span class="block text-xs text-red-500">Invalid File Format</span>
                                    @enderror
                                </label>
                                <p class="text-sm">

                                    <span>Upload Printable Stack</span>
                                    <span class="block text-xs text-white">Image and Design Files only</span>

                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- Upload Modal End --}}
                </form>
                {{-- Form Container End --}}
            </div> {{-- Background Image Container End --}}
        </div> {{-- Main Content Area End --}}
    </div> {{-- Main Layout End --}}
</div>
