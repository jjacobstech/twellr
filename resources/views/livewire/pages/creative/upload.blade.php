<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public function uploadDesignStack()
    {
    }
    public function uploadPrintableStack()
    {
    }
}; ?>

<div x-data="{
    uploadDesignModal: true,
    uploadPrintModal: false,
    uploadSection: false,
}">
    <div class="flex gap-1">
        <x-creative-sidebar />
        <div class="text-xl w-[100%] p-4 bg-white">
            <div style="background-image: url('{{ asset('assets/blurred.png') }}')"
                class="rounded-lg text-white h-screen sm:h-screen md:h-full lg:h-full bg-cover bg-no-repeat justify-center">
                {{-- Upload Buttons --}}
                <div class="grid md:flex w-full md:space-x-28 justify-center md:pt-40" x-show="uploadSection"
                    x-cloak="display:none">
                    <div class="justify-center grid ">
                        <p class="md:hidden text-center">Upload To Design Stack</p>
                        <img @click="uploadSection = false, uploadDesignModal = true" class="h-40 w-40 rounded-2xl"
                            src="{{ asset('assets/uploadDesignStack.png') }}" alt="">
                    </div>
                    <span class="my-14 text-4xl text-gray-200 font-bold text-center">{{ __('or') }}</span>
                    <div class="justify-center ">
                        <p class="md:hidden text-center">Upload To Printable Stack</p>

                        <img @click="uploadSection = false, uploadPrintModal = true" class="h-40 w-40 rounded-2xl"
                            src="{{ asset('assets/uploadPrintableStack.png') }}" alt="">
                    </div>
                </div>
                <div class="hidden md:flex justify-center font-extrabold p-0 gap-52 my-5 text-sm" x-show="uploadSection"
                    x-cloak="display:none">
                    <p class=" text-center">Upload To Design Stack</p>

                    <p class="ml-12">Upload To Printable Stack</p>

                </div>
                {{-- Upload Buttons End --}}

                {{-- Printable Stack upload --}}
                <div class="p-4 px-5 flex justify-center w-100" x-show="uploadPrintModal" x-cloak="display:none">
                    <div class="w-[20%] pt-56 float-left">
                        <img class="mt-52 w-16 h-16 rounded-full ml-7"
                            @click="uploadSection = true, uploadPrintModal = false"
                            src="{{ asset('assets/uploadPrintableStack.png') }}" alt="">
                    </div>
                    <div class="w-[70%] flex py-2">
                        <form class="bg-[#dedddb] rounded-[40px]  block w-[70%]  mt-3" action="">
                            <div class="p-10">
                                <div class="flex w-full relative">
                                    <div class=" w-1/2 mr-32">
                                        <x-input-label class="font-extrabold text-gray-500 text-[17px]" for="email"
                                            :value="__('Name')" />

                                        <div class="w-full mt-1">
                                            <x-text-input wire:model="form.email" id="email"
                                                class="inline-block w-full mt-1 border-[#bebebe] bg-[#bebebe] border-0 rounded-xl"
                                                type="email" name="email" required autofocus autocomplete="email" />
                                            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                                        </div>
                                    </div>
                                    <div class=" w-1/4">
                                        <x-input-label class="font-extrabold text-gray-500 text-[17px]" for="email"
                                            :value="__('Price')" />

                                        <div class="w-full mt-1">
                                            <x-text-input wire:model="form.email" id="email"
                                                class="inline-block w-full mt-1 border-[#bebebe] bg-[#bebebe] border-0 rounded-xl"
                                                type="email" name="email" required autofocus autocomplete="email" />
                                            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                                <div class=" mt-5">
                                    <div class=" w-100">
                                        <x-input-label class="font-extrabold text-gray-500 text-[17px]" for="email"
                                            :value="__('Category')" />

                                        <div class="w-100 mt-1">
                                            <x-text-input wire:model="form.email" id="email"
                                                class="inline-block w-full mt-1 border-[#bebebe] bg-[#bebebe] border-0"
                                                type="email" name="email" required autofocus autocomplete="email" />
                                            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                                <div class="flex mt-5">
                                    <div class="relative w-[60%] mr-20">
                                        <x-input-label class="font-extrabold text-gray-500 text-[17px] " for="email"
                                            :value="__('Description')" />

                                        <div class="w-full mt-1">

                                            <x-textarea-input wire:model="form.email" id="email"
                                                class="inline-block w-full mt-1 border-[#bebebe] bg-[#bebebe]  border-0 h-32 rounded-xl"
                                                type="email" name="email" required autofocus
                                                autocomplete="email"></x-textarea-input>
                                            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                                        </div>
                                    </div>
                                    <div class="relative w-[35%] ">
                                        <x-input-label class="pl-20 font-extrabold text-gray-500 text-[17px]"
                                            for="email" :value="__('Photos')" />
                                        <div class="w-full mt-3">

                                            <label for="dropzone-file"
                                                class=" w-full h-full  cursor-pointer rounded-[40px]  ">

                                                {{-- <img class="w-32 h-32 rounded-"
                                                    src="{{ asset('assets/photos-icon.png') }}" alt=""> --}}
                                                <svg fill="#888a85" viewBox="0 0 35 35" data-name="Layer 2"
                                                    id="f7b9d31b-3db4-48db-9630-5b4624996f58"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                        stroke-linejoin="round"></g>
                                                    <g id="SVGRepo_iconCarrier">
                                                        <path
                                                            d="M29.467,34.749H5.53A5.288,5.288,0,0,1,.25,29.467V5.532A5.286,5.286,0,0,1,5.53.252H29.467a5.286,5.286,0,0,1,5.28,5.28V29.467A5.288,5.288,0,0,1,29.467,34.749ZM5.53,2.752a2.783,2.783,0,0,0-2.78,2.78V29.467a2.784,2.784,0,0,0,2.78,2.782H29.467a2.784,2.784,0,0,0,2.78-2.782V5.532a2.783,2.783,0,0,0-2.78-2.78Z">
                                                        </path>
                                                        <path
                                                            d="M11.86,17.226a4.468,4.468,0,1,1,4.468-4.468A4.473,4.473,0,0,1,11.86,17.226Zm0-6.435a1.968,1.968,0,1,0,1.968,1.967A1.97,1.97,0,0,0,11.86,10.791Z">
                                                        </path>
                                                        <path
                                                            d="M2.664,31.92a1.25,1.25,0,0,1-.929-2.085l5.876-6.547a3.288,3.288,0,0,1,4.553-.341l2.525,2.084a.77.77,0,0,0,.6.178.794.794,0,0,0,.543-.3l6.644-8.584a3.277,3.277,0,0,1,2.6-1.279h.012A3.282,3.282,0,0,1,27.673,16.3l6.372,8.107a1.25,1.25,0,0,1-1.966,1.545l-6.372-8.107a.785.785,0,0,0-.627-.3.864.864,0,0,0-.631.309L17.8,26.434a3.3,3.3,0,0,1-4.707.525l-2.525-2.084a.794.794,0,0,0-1.1.083L3.6,31.5A1.245,1.245,0,0,1,2.664,31.92Z">
                                                        </path>
                                                    </g>
                                                </svg>

                                                <input id="dropzone-file" type="file" class="hidden" />
                                            </label>
                                        </div>
                                        {{-- <x-input-label class="font-extrabold text-gray-500 text-[17px]" for="email"
                                            :value="__('File')" />

                                        <div class="w-full mt-4">
                                            <x-file-input wire:model="form.email" :type="__('file')" id="email"
                                                class="inline-block w-full mt-1 border-[#bebebe] bg-[#bebebe] border-0 rounded-xl"
                                                name="email" required autofocus autocomplete="email" />
                                            <x-input-error :messages="$errors->get('form.email')" class="mt-2" /> --}}
                                        {{-- </div> --}}
                                    </div>
                                </div>
                            </div>
                            <p class="border-t-2 border-[#bebebe] m-0"></p>

                            <div class="py-4 flex justify-center">
                                <button
                                    class="bg-[#f7aa10] inline-block mt-0 text-gray-500 px-6 py-2.5 rounded-xl font-bold uppercase text-[15px]">Monitize
                                    Design </button>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- Printable Stack upload End --}}

                {{-- Design Stack upload --}}
                <div class="p-4 px-5 flex justify-center w-100 " x-show="uploadDesignModal" x-cloak="display:none">
                    <div class="w-[20%] pt-56 float-left">
                        <img class="mt-52 w-16 h-16 rounded-full ml-7"
                            @click="uploadSection = true, uploadDesignModal = false"
                            src="{{ asset('assets/uploadDesignStack.png') }}" alt="">
                    </div>

                    <div class="w-[80%] justify-center  py-2">
                        <p class="w-[70%] text-[#bebebe] text-center mt-20 font-bold">Choose File To Upload / Drag &
                            Drop</p>

                        <form id="dropzone" enctype="multipart/form-data"
                            class="h-72 mt-3 bg-transparent border-[#bebebe] border-2 border-dashed rounded-[10px]  block w-[70%]  hover:border-gray-500 hover:border-dashed hover:border-2 dropzone"
                            action="{{ route('upload.print.stack') }}" title="Drag a file or Click to Upload">
                            <label for="dropzone-file"
                                class="dz-message flex flex-col items-center justify-center w-full h-full  cursor-pointer rounded-[40px]  ">

                                <img class="w-32 h-32" src="{{ asset('assets/add-file.svg') }}" alt="">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-info-circle mt-5 ml-60 absolute w-10 h-10 "
                                        viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                        <path
                                            d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0" />
                                    </svg>
                                </span>
                                <input id="dropzone-file" type="file" class="hidden" />
                            </label>
                            <div id="file-uploader"></div>
                            <div id="preview-template" class="hidden">
                                <div class="dz-preview dz-file-preview">
                                    <div class="dz-image">
                                        <img data-dz-thumbnail />
                                    </div>
                                    <div class="dz-details">
                                        <div class="dz-filename"><span data-dz-name></span></div>
                                        <div class="dz-size" data-dz-size></div>
                                    </div>
                                    <div class="dz-progress">
                                        <span class="dz-upload" data-dz-uploadprogress></span>
                                    </div>
                                    <div class="dz-error-message">
                                        <span data-dz-errormessage></span>
                                    </div>
                                    <div class="dz-success-mark">✔</div>
                                    <div class="dz-error-mark">✘</div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                {{-- Design Stack upload End --}}
            </div>
        </div>

    </div>

    {{-- <script>
        document.getElementById('button').setAttribute('style', 'display:none');

        const handleFileSelect = (input) => {

            const fileType = input.files[0].type



            if (input.files && input.files[0]) {
                if (fileType === 'application/pdf') {
                    // const fileSizeInBytes = input.files[0].size
                    // console.log(fileSizeInBytes)
                    // if (fileSizeInBytes > 16777216) {
                    const fileName = input.files[0].name;
                    const fileSize = (input.files[0].size / (1024 * 1024)).toFixed(2) + "MB";
                    document.getElementById('fileName').textContent = fileName;
                    document.getElementById('fileSize').textContent = fileSize;
                    document.getElementById('button').setAttribute('style', 'display:block');
                    // } else {
                    //     alert('This file is too Big')
                    // }




                }
            }
        }

        document.getElementById('dropzone-file').addEventListener('alpine:init', () => {
            Alpine.data('fileUpload', () => ({
                async uploadFile(event) {
                    const file = event.target.files[0];
                    const formData = new FormData();
                    formData.append('file', file);

                    try {
                        const response = await fetch('/upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('File upload failed');
                        }

                        const result = await response.json();
                        console.log('File uploaded successfully', result);
                    } catch (error) {
                        console.error('Error uploading file:', error);
                    }
                }
            }));
        });
    </script> --}}
</div>
