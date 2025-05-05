<?php

use Mary\Traits\Toast;
use App\Models\Contest;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\FileHelper;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;
    use Toast;

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
    public $designFest = false;

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function toggleDesignFest()
    {
        $this->designFest = !$this->designFest;
    }

    public function uploadProduct()
    {
        $product = (object) ($validated = $this->validate([
            'printUpload' => ['required', 'file', 'mimes:' . config('twellr.printable_stack_format')],
            'sideView' => ['required', 'file', 'mimes:' . config('twellr.design_stack_format')],
            'backView' => ['required', 'file', 'mimes:' . config('twellr.design_stack_format')],
            'frontView' => ['required', 'file', 'mimes:' . config('twellr.design_stack_format')],
            'description' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'name' => 'required|string',
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
            'category_id' => $product->category,
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
            $this->error('Product Upload Failed', 'Something happened but we are working on it');
        }
        else{
              if ($this->designFest === true) {
           $contest = Contest::create([
                'user_id' => Auth::id(),
                'product_id' => $productSaved->id,
                'category_id' => $product->category,
                'type' => 'design_fest'
            ]);

            if(!$contest){
                $productSaved->delete();
                 $this->error('Product Upload Failed', 'Unable to enter Design Fest');
            }
            else{
                     $this->cleanupOldUploads();

 if ($this->designFest === true) {
           session()->flash('designFest', 'true');
 }

        session()->flash('status', 'true');

        $this->redirectIntended(route('creative.upload'), true);
            }

        }


        }
            $this->cleanupOldUploads();
    }
}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,
}" x-on:livewire-upload-error="$wire.error('Upload Failed')"
    x-on:livewire-upload-finish="$wire.success('Upload Successful')"
    class="h-screen pb-20 overflow-y-scroll font-sans md:pb-0 scrollbar-none">

    @session('status')
        {{ $this->success('Design Upload Successful', 'Check Marketplace to view design') }}
    @endsession
   @session('designFest')
        {{ $this->success('Design Entered Into Design Fest', 'Check Contest Page to view Design') }}
    @endsession
    <!-- Error notifications -->
    @error('printUpload')
        {{ $this->error('Print File Upload Error', 'Upload Print File') }}
    @enderror

    @error('sideView')
        {{ $this->error('Side View Upload Error', 'Upload Side View') }}
    @enderror

    @error('backView')
        {{ $this->error('Back View Upload Error', 'Upload Back View') }}
    @enderror

    @error('frontView')
        {{ $this->error('Front View Upload Error', 'Upload Front View') }}
    @enderror

    @error('description')
        {{ $this->error('Description Field is empty') }}
    @enderror

    @error('category')
        {{ $this->error('Category Field is empty') }}
    @enderror

    @error('price')
        {{ $this->error('Price Field is empty') }}
    @enderror

    @error('name')
        {{ $this->error('Name Field is empty') }}
    @enderror

    <!-- Main Layout: Sidebar + Content -->
    <div class="flex flex-col h-screen bg-gray-100 md:flex-row md:gap-1 scrollbar-none">
        <!-- Sidebar - Adjusts width proportionally -->
        <x-creative-sidebar class="w-full md:w-[12%] md:min-h-screen" />

        <!-- Main Content Area - Takes remaining space -->
        <div class="w-full md:w-[87%] px-2 sm:px-4 py-2.5 text-xl bg-white h-screen md:h-full scrollbar-none">
            <!-- Background Container - Height adapts to content -->
            <div style="background-image: url('{{ asset('assets/blurred.png') }}')"
                class="relative flex justify-center text-white bg-no-repeat bg-cover rounded-lg min-h-[500px] h-100 scrollbar-none">

                <!-- Back Button - Positioned consistently across screens -->
                <div class="absolute z-10 top-2 sm:top-4 left-2 sm:left-4">
                    <img x-show="backButton" x-transition:enter.duration.500ms x-cloak
                        @click="form = true, uploadModal = false, backButton = false"
                        class="w-10 h-10 transition cursor-pointer sm:w-12 sm:h-12 md:w-16 md:h-16 hover:scale-110"
                        src="{{ asset('assets/back-arrow.png') }}" alt="Back">
                </div>

                <!-- Form Container - Adapts width based on screen size and state -->
                <form x-transition:enter.duration.500ms x-cloak="display:none"
                    :class="uploadModal ? 'w-full p-2 sm:p-4 md:p-8 lg:p-12' :
                        'w-[95%] sm:w-[90%] md:w-[75%] lg:w-[60%] bg-[#dedddb] rounded-[20px] sm:rounded-[30px] md:rounded-[40px] my-4 sm:my-6'"
                    wire:submit="uploadProduct" enctype="multipart/form-data">
                    @csrf

                    <!-- Main Form View -->
                    <div x-show="form" x-transition:enter.duration.700ms>
                        <div class="flex flex-col h-full px-3 py-4 sm:px-6 md:px-10 sm:py-6 md:py-10">

                            <!-- Name and Price Row - Always stack on mobile, side by side on larger screens -->
                            <div class="flex flex-col sm:flex-row sm:gap-4 md:gap-6">
                                <div class="flex-1 w-full mb-4 sm:mb-0">
                                    <x-input-label :value="__('Name')"
                                        class="text-gray-500 font-extrabold text-[15px] sm:text-[17px]"
                                        for="name" />
                                    <x-text-input id="name" :class="$errors->get('name')
                                        ? 'block w-full mt-1 sm:mt-2 ring-1 border-0 ring-red-600 bg-[#bebebe] rounded-xl'
                                        : 'block border-0 w-full mt-1 sm:mt-2 bg-[#bebebe] rounded-xl'" type="text" name="name"
                                        wire:model.live="name" autofocus autocomplete="name" />
                                </div>

                                <div class="w-full sm:w-[40%] md:w-[30%]">
                                    <x-input-label :value="__('Price')"
                                        class="text-gray-500 font-extrabold text-[15px] sm:text-[17px]"
                                        for="price" />
                                    <x-text-input id="price" :class="$errors->get('price')
                                        ? 'block w-full mt-1 sm:mt-2 ring-1 border-0 ring-red-600 bg-[#bebebe] rounded-xl'
                                        : 'block border-0 w-full mt-1 sm:mt-2 bg-[#bebebe] rounded-xl'" wire:model.live="price"
                                        type="text" name="price" autofocus autocomplete="price" />
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="mt-3 sm:mt-4">
                                <x-input-label :value="__('Category')"
                                    class="text-gray-500 font-extrabold text-[15px] sm:text-[17px]" for="category" />
                                <x-select id="category" wire:model.live="category" :class="$errors->get('category')
                                    ? 'block w-full mt-1 sm:mt-2 border-0 ring-red-600 bg-[#bebebe] ring-1 rounded-xl'
                                    : 'block w-full mt-1 sm:mt-2 border-[#bebebe] bg-[#bebebe] border-0 rounded-xl'" type="text"
                                    name="category" autofocus autocomplete="category">
                                    <x-slot name="options">
                                        <option class="text-black" disabled value="">Select a category</option>
                                        @foreach ($categories as $category)
                                            <option class="text-black"  value="{{ $category->id }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </x-slot>
                                </x-select>
                            </div>

                            <!-- Description and Uploads Row - Stack on small screens, side by side on larger -->
                            <div class="flex flex-col mt-3 sm:mt-4 md:flex-row md:gap-4 lg:gap-6">
                                <div class="w-full md:w-3/5">
                                    <x-input-label :value="__('Description')"
                                        class="text-gray-500 font-extrabold text-[15px] sm:text-[17px]"
                                        for="description" />
                                    <x-textarea-input wire:model.live="description" :class="$errors->get('description')
                                        ? 'w-full ring-1 border-0 ring-red-600 px-2 py-1 mt-1 sm:mt-2 bg-[#bebebe] rounded-xl text-black h-20 sm:h-24 md:h-28 scrollbar-none'
                                        : 'w-full px-2 py-1 mt-1 sm:mt-2 bg-[#bebebe] border-0 rounded-xl text-black h-20 sm:h-24 md:h-28 scrollbar-none'"
                                        id="description"></x-textarea-input>
                                </div>

                                <div
                                    class="flex flex-col items-center w-full mt-3 sm:mt-4 md:w-2/5 md:mt-0 md:items-end">
                                    <x-input-label :value="__('Uploads')"
                                        class="text-gray-500 font-extrabold text-[15px] sm:text-[17px] mb-1 sm:mb-2 text-center md:text-right"
                                        for="uploads" />
                                    <img @click="form=false, uploadModal=true, backButton=true"
                                        class="h-16 w-16 sm:h-20 sm:w-20 md:h-24 md:w-24 lg:h-28 lg:w-28 hover:cursor-pointer rounded-xl sm:rounded-2xl {{ $errors->has('frontView') ||
                                        $errors->has('backView') ||
                                        $errors->has('sideView') ||
                                        $errors->has('printUpload')
                                            ? 'ring-1 ring-red-600 border-0'
                                            : '' }}"
                                        src="{{ asset('assets/image.png') }}" alt="Upload Trigger">
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t-2 border-[#bebebe] w-full"></div>

                        <!-- Submit Button -->
                        <div class="grid justify-center py-4 space-y-2 sm:py-4">
                            <div class="flex justify-center gap-1">
                                <span wire:click="toggleDesignFest"
                                    class="{{ $designFest ? 'bg-navy-blue hover:bg-navy-blue' : 'bg-gray-300 hover:bg-gray-400' }} relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none">
                                    <span class="sr-only">Submit To Design Fest</span>
                                    <span
                                        class="{{ $designFest ? 'translate-x-6' : 'translate-x-1' }} inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
                                </span>
                                <span class="text-sm text-gray-500">Submit To Design Fest</span>
                            </div>
                            <x-mary-button label="MONETIZE DESIGN"
                                class="btn hover:bg-navy-blue hover:text-white active:bg-navy-blue bg-[#f7aa10] text-neutral-900 px-4 sm:px-6 py-3 sm:py-4 rounded-lg sm:rounded-xl font-bold uppercase text-[12px] sm:text-[13px] focus:ring-0 focus:bg-navy-blue focus:text-white border-0"
                                type="submit" spinner />


                        </div>
                    </div>
                    <!-- Form End -->

                    <!-- Upload Modal -->
                    <div x-show="uploadModal" x-transition:enter.duration.500ms x-cloak
                        class="py-6 sm:py-8 md:py-16 lg:py-[124.5px]">
                        <!-- Responsive Grid Layout for Uploads -->
                        <div
                            class="grid grid-cols-1 gap-4 px-2 xs:grid-cols-2 lg:grid-cols-4 sm:gap-6 md:gap-8 sm:px-4 md:px-8 lg:px-16">

                            <!-- Front View Upload -->
                            <div class="grid content-start justify-center space-y-1 text-center sm:space-y-2">
                                <x-mary-file omit-error="true"
                                    class="relative grid items-center w-24 h-24 mx-auto xs:w-28 xs:h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 lg:w-40 lg:h-40 group"
                                    change-text="Upload Front View" wire:model.live="frontView"
                                    accept="image/png, image/jpeg, image/jpg">
                                    <!-- Responsive Image Container -->

                                    <img class="object-cover w-full h-full rounded-xl"
                                        src="{{ asset('assets/uploadDesignStack.png') }}" alt="Upload Front View">

                                </x-mary-file>
                                @error('frontView')
                                    <span class="block text-xs text-red-600">Invalid File Format</span>
                                @enderror
                                <p class="text-sm">
                                    <span>Front View</span>
                                    <span class="block text-xs text-gray-100">jpeg, png, jpg only</span>
                                </p>
                            </div>

                            <!-- Back View Upload -->
                            <div class="grid content-start justify-center space-y-1 text-center sm:space-y-2">

                                <x-mary-file omit-error="true"
                                    class="relative grid items-center w-24 h-24 mx-auto xs:w-28 xs:h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 lg:w-40 lg:h-40 group"
                                    change-text="Upload Back View" wire:model.live="backView"
                                    accept="image/png, image/jpeg, image/jpg">

                                    <img class="object-cover w-full h-full rounded-xl"
                                        src="{{ asset('assets/uploadDesignStack.png') }}" alt="Upload Back View">
                                </x-mary-file>
                                @error('backView')
                                    <span class="block text-xs text-red-600">Invalid File Format</span>
                                @enderror

                                <p class="text-sm">
                                    <span>Back View</span>
                                    <span class="block text-xs text-gray-100">jpeg, png, jpg only</span>
                                </p>
                            </div>

                            <!-- Side View Upload -->
                            <div class="grid content-start justify-center space-y-1 text-center sm:space-y-2">
                                <x-mary-file  omit-error="true"
                                    class="relative grid items-center w-24 h-24 mx-auto xs:w-28 xs:h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 lg:w-40 lg:h-40 group"
                                    change-text="Upload Side View" wire:model="sideView"
                                    accept="image/png, image/jpeg, image/jpg">
                                    <img class="object-cover w-full h-full rounded-xl"
                                        src="{{ asset('assets/uploadDesignStack.png') }}" alt="Upload Side View">
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

                            <!-- Printable Stack Upload -->
                            <div class="grid content-start justify-center space-y-1 text-center sm:space-y-2">
                                <label
                                    title="Upload Print file ({{ config('twellr.printable_stack_format', 'Allowed Formats') }})">
                                    <input class="hidden" type="file" accept="*"
                                        wire:model.live="printUpload" name="printable_stack" id="printable_stack">

                                    <div
                                        class="relative w-24 h-24 mx-auto xs:w-28 xs:h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 lg:w-40 lg:h-40 group">
                                        <div class="relative z-40 flex items-center justify-center w-full h-full p-2 transition-all duration-150 bg-white cursor-pointer group-hover:scale-110 group-hover:shadow-xl rounded-xl"
                                            id="printable-container">
                                            <!-- Display file icon or placeholder -->
                                            <img class="object-contain w-full h-full"
                                                src="{{ $printUpload ? asset('assets/file.png') : asset('assets/uploadPrintableStack.png') }}"
                                                id="printImage" alt="Printable Stack Upload">
                                        </div>
                                        <div
                                            class="absolute inset-0 z-30 flex items-center justify-center w-full h-full transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                        </div>
                                    </div>

                                </label>
                                <p class="text-sm">
                                     @error('printUpload')
                                        <span class="block text-xs text-red-500">Invalid File Format</span>
                                    @enderror
                                    <span>Upload Printable Stack</span>
                                    <span class="block text-xs text-white">Image and Design Files only</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Upload Modal End -->
                </form>
                <!-- Form Container End -->
            </div> <!-- Background Image Container End -->
        </div> <!-- Main Content Area End -->
    </div> <!-- Main Layout End -->
</div>
