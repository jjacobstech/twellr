<?php

use App\Models\Product;
use App\Helpers\FileHelper;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;
    public string $name = 'Joshua';
    public string $price = '2000';
    public string $category = 'Test';
    public string $description = 'Test';
    public $designUpload;
    public $printUpload;
    public bool $spinner = false;

    public function uploadProduct()
    {
        $product = (object) ($validated = $this->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'description' => 'required|string',
            'designUpload' => ['required', 'file', 'mimes:' . config('twellr.design_stack_format')],
            'printUpload' => ['required', 'file', 'mimes:' . config('twellr.printable_stack_format')],
        ]));

        $designFile = $product->designUpload;
        $printFile = $product->printUpload;

        //File Data Fetch
        $designFileData = FileHelper::getFileData($designFile);
        $printFileData = FileHelper::getFileData($printFile);

        // Save Files Into Storage
        $designFileSave = FileHelper::saveFile($designFile, 'products/design-stack/' . $designFileData->name);
        $printFileSave = FileHelper::saveFile($printFile, 'products/print-stack/' . $printFileData->name);

        if (!($designFileSave && $printFileSave)) {
            abort(500);
        }

        $productSaved = Product::create([
            'user_id' => auth()->user()->id,
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
            'description' => $product->description,
            'print_stack' => $printFileData->name,
            'print_stack_mime' => $printFileData->mime,
            'print_stack_extension' => $printFileData->extension,
            'print_stack_size' => $printFileData->size,
            'design_stack' => $designFileData->name,
            'design_stack_mime' => $designFileData->mime,
            'design_stack_extension' => $designFileData->extension,
            'design_stack_size' => $designFileData->size,
            'status' => 'available',
        ]);

        if (!$productSaved) {
            abort(500);
        }

        $this->cleanupOldUploads();

        session()->flash('status', 'true');
        redirect(route('creative.upload'));
    }
}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,
}"
    x-on:livewire-upload-finish ="showNotification('Upload Complete', 'File Upload Successful' , 'success' , 5)">

    @if (session('status'))
        @script
            <script>
                showNotification('Product Added', 'Product Added Successful', 'success', 5)
            </script>
        @endscript
    @endif
    <div class="flex gap-1">
        @if ($errors->get('designUpload') || $errors->get('printUpload'))
            @script
                <script>
                    showNotification('Invalid File Format',
                        "Check Uploads", 'error', 5)
                </script>
            @endscript
        @endif

        <x-creative-sidebar />
        <div class="text-xl w-[100%] p-4 bg-white h-full ">
            <div style="background-image: url('{{ asset('assets/blurred.png') }}')"
                class="grid justify-center text-white bg-no-repeat bg-cover rounded-lg sm:h-screen md:h-full lg:h-100">

                {{-- Form Begin --}}
                <form x-transition:enter.duration.500ms x-cloak="display:none"
                    :class="uploadModal ? 'py-4' : 'w-[100%]  bg-[#dedddb] rounded-[40px] my-6'" action=""
                    wire:submit.prevent='uploadProduct' enctype="multipart/form-data">
                    @csrf
                    <div x-show="form" x-transition:enter.duration.500ms>
                        <div class="flex flex-col p-10 py-6">
                            <div class="flex mt-4 ">
                                <div class="flex-1 w-[60%]">
                                    <x-input-label :value="__('Name')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="name" />
                                    <x-text-input id="name" :class="$errors->get('name')
                                        ? 'inline-block w-100 mt-2  border-1 border-red-600 bg-[#bebebe] rounded-xl'
                                        : 'border-0 inline-block w-100 mt-2 bg-[#bebebe] rounded-xl '" type="text" name="name"
                                        wire:model='name' required autofocus autocomplete="name" />
                                </div>

                                <div class="flex-1 w-[20%] ml-[20%]">
                                    <x-input-label :value="__('Price')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="price" />
                                    <x-text-input id="price" :class="$errors->get('price')
                                        ? 'inline-block w-full mt-2 border-1 border-red-600 bg-[#bebebe] rounded-xl '
                                        : 'border-0 inline-block w-full mt-2 bg-[#bebebe] rounded-xl '" wire:model='price' type="text"
                                        name="price" required autofocus autocomplete="price" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label :value="__('Category')" class="text-gray-500 font-extrabold text-[17px]"
                                    for="category" />
                                <x-text-input id="category" wire:model='category' :class="$errors->get('category')
                                    ? 'inline-block w-full mt-2  border-red-600 bg-[#bebebe] border-1 rounded-xl'
                                    : 'inline-block w-full mt-2  border-[#bebebe] bg-[#bebebe] border-0 rounded-xl'" type="text"
                                    name="category" required autofocus autocomplete="category" />
                            </div>
                            <div class="flex mt-4 ">
                                <div class="w-[50%]">
                                    <x-input-label :value="__('Description')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="description" />
                                    <x-textarea-input wire:model='description' :class="$errors->get('description')
                                        ? 'w-full border-1 border-red-600 px-2 py-1 mt-2 text-white h-28'
                                        : 'w-full px-2 py-1 mt-2 text-white h-28'"
                                        id="description"></x-textarea-input>
                                </div>

                                <div class="w-[30%] ml-[20%]">
                                    <x-input-label :value="__('Uploads')"
                                        class="text-gray-500 font-extrabold text-[17px] ml-[40%]" for="name" />
                                    <img x-show="progress = 0 || progress = 100"
                                        @click="form=false, uploadModal=true, backButton = true"
                                        class="{{ $errors->get('designUpload') || $errors->get('printUpload')
                                            ? 'mt-2 h-28 w-28 hover:cursor-pointer border  border-red-600 rounded-2xl'
                                            : 'mt-2 h-28 w-28 hover:cursor-pointer rounded-2xl' }}"
                                        src="{{ asset('assets/image.png') }}" alt="">
                                </div>


                            </div>
                        </div>

                        <p class="border-t-2 border-[#bebebe] m-0 w-100"></p>

                        <div class="flex justify-center py-5">
                            <x-bladewind.button type="submit"
                                class="bg-[#f7aa10] inline-block mt-0 text-neutral-900 px-6 py-4 rounded-xl font-bold uppercase text-[13px] focus:ring-0 focus:bg-navy-blue focus:text-white"
                                x-cloak="display:none" has_spinner="true" wire:loading.remove wire:click='uploadProduct'
                                :outline="false">Monitize
                                Design</x-bladewind.button>
                            <x-bladewind.button type="submit" x-cloak="display:none"
                                class="bg-[#f7aa10] inline-block mt-0 text-neutral-900 px-6 py-4 rounded-xl font-bold uppercase text-[13px] focus:ring-0 focus:bg-navy-blue focus:text-white"
                                :has_spinner="true" show_spinner='true' wire:loading>Monitize
                                Design</x-bladewind.button>

                        </div>
                    </div>
                    {{-- Form End --}}

                    {{-- Upload Buttons --}}

                    <div x-show="uploadModal" x-transition:enter.duration.500ms x-cloak="display:none"
                        class="justify-between py-[92px]">
                        <div class="my-10 py-0.5">
                            <div class="flex justify-between w-full md:flex md:space-x-24 md:py-2 ">

                                <div class="grid justify-center ">
                                    {{-- <p class="text-center hidden">Upload To Design Stack</p> --}}

                                    <label for="design_stack"
                                        title="Upload Design image({{ config('twellr.design_stack_formats') }}) only...">
                                        <input class="hidden" type="file" accept="image/*" wire:model='designUpload'
                                            name="design_stack" id="design_stack">
                                        <div class="relative w-full group">
                                            <div
                                                class="relative z-40 flex items-center justify-center w-40 h-40 mx-auto transition-all duration-500 bg-white cursor-pointer group-hover:translate-x-8 group-hover:shadow-2xl group-hover:-translate-y-8 rounded-xl">

                                                <img class="w-40 h-40 rounded-xl @error('designUpload')
                                        border-2 border-red-600
                                    @enderror "
                                                    src="@if ($designUpload == null) {{ asset('assets/uploadDesignStack.png') }}@else {{ $designUpload->temporaryUrl() }} @endif"
                                                    id="design_stack" alt="">
                                            </div>
                                            <div
                                                class="absolute inset-0 z-30 flex items-center justify-center w-40 h-40 mx-auto transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="grid justify-center pr-3">
                                    {{-- <p class="text-center  sm:hidden">Upload To Printable Stack</p> --}}

                                    <label for="printable_stack"
                                        title="Upload Print file({{ config('twellr.printable_stack_format') }})...">
                                        <input class="hidden" type="file" accept="*" wire:model='printUpload'
                                            name="printable_stack" id="printable_stack">

                                        <div class="relative w-full group ">
                                            <div class="relative z-40 flex   items-center justify-center w-40 h-40 mx-auto transition-all duration-500 bg-white cursor-pointer group-hover:translate-x-8 group-hover:shadow-2xl group-hover:-translate-y-8 rounded-xl  "
                                                id="printable-contaner">
                                                <img class="w-40 h-40 rounded-xl @error('printUpload')
                                        border-2 border-red-600
                                    @enderror @if ($printUpload) p-5 @endif"
                                                    src="@if ($printUpload != null) {{ asset('assets/file.png') }}@else{{ asset('assets/uploadPrintableStack.png') }} @endif"
                                                    id="printImage" alt="">
                                            </div>
                                            <div
                                                class="absolute inset-0 z-30 flex items-center justify-center w-40 h-40 mx-auto transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                            </div>
                                        </div>
                                    </label>

                                </div>
                            </div>
                            <div class="justify-center pl-4  text-sm font-extrabold md:flex gap-52"
                                x-cloak="display:none">
                                <p class="grid justify-center text-center">
                                    <span>Upload Design Stack</span>
                                    <span>jpeg, png, jpg only</span>
                                    @error('designUpload')
                                        <span class="text-red-500 fixed mt-12 mx-2">Invalid File Format</span>
                                    @enderror
                                    <span class=" fixed mt-16 mx-2">

                                    </span>

                                </p>

                                <p class="grid justify-center text-center ">
                                    <span>Upload Printable Stack</span>
                                    <span>Image and Design Files only</span>
                                    @error('printUpload')
                                        <span class="text-red-500 fixed mt-10 pt-1 mx-8">Invalid File Format</span>
                                    @enderror
                                </p>

                            </div>
                        </div>
                        {{-- Upload Buttons End --}}


                    </div>
                </form>
                <div class="absolute w-100 mt-[30%]  ">
                    <img x-show="backButton" x-transition:enter.duration.500ms x-cloak="display:none"
                        @click="form = true, uploadModal = false, backButton = false"
                        class="w-16 h-16 ml-10 transition hover:scale-110" src="{{ asset('assets/back-arrow.png') }}"
                        alt="">
                </div>
            </div>
        </div>
    </div>

</div>
</div>
