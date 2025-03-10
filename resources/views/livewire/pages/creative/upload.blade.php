<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public function uploadDesignStack() {}
    public function uploadPrintableStack() {}
}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,

}">
    <div class="flex gap-1">
        <x-creative-sidebar />
        <div class="text-xl w-[100%] p-4 bg-white h-full ">
            <div style="background-image: url('{{ asset('assets/blurred.png') }}')"
                class="grid justify-center text-white bg-no-repeat bg-cover rounded-lg sm:h-screen md:h-full lg:h-100">
                {{-- Form Begin --}}
                <form x-transition:enter.duration.500ms x-cloak="display:none"
                    :class="uploadModal ? 'py-4' : 'w-[100%]  bg-[#dedddb] rounded-[40px] my-6'" action=""
                    method="post" enctype="multipart/form-data">
                    @csrf
                    <div x-show="form" x-transition:enter.duration.500ms>
                        <div class="flex flex-col p-10 py-6">
                            <div class="flex mt-4 ">
                                <div class="flex-1 w-[60%]">
                                    <x-input-label :value="__('Name')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="name" />
                                    <x-text-input id="email"
                                        class="inline-block w-100 mt-2 lowercase border-[#bebebe] bg-[#bebebe] border-0 rounded-xl"
                                        type="text" name="name" required autofocus autocomplete="email" />
                                    <x-input-error :messages="__('')" class="mt-2" />
                                </div>

                                <div class="flex-1 w-[20%] ml-[20%]">
                                    <x-input-label :value="__('Price')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="name" />
                                    <x-text-input id="email"
                                        class="inline-block w-full mt-2 lowercase border-[#bebebe] bg-[#bebebe] border-0 rounded-xl"
                                        type="text" name="name" required autofocus autocomplete="email" />
                                    <x-input-error :messages="__('')" class="mt-2" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label :value="__('Category')" class="text-gray-500 font-extrabold text-[17px]"
                                    for="name" />
                                <x-text-input id="email"
                                    class="inline-block w-full mt-2 lowercase border-[#bebebe] bg-[#bebebe] border-0 rounded-xl"
                                    type="text" name="name" required autofocus autocomplete="email" />
                                <x-input-error :messages="__('')" class="mt-2" />
                            </div>
                            <div class="flex mt-4 ">
                                <div class="w-[50%]">
                                    <x-input-label :value="__('Description')" class="text-gray-500 font-extrabold text-[17px]"
                                        for="name" />
                                    <x-textarea-input class="w-full px-2 py-1 mt-2 text-white h-28"
                                        id="description"></x-textarea-input>
                                </div>
                                <div class="w-[30%] ml-[20%]">
                                    <x-input-label :value="__('Uploads')"
                                        class="text-gray-500 font-extrabold text-[17px] ml-[40%]" for="name" />
                                    <img @click="form=false, uploadModal=true, backButton = true"
                                        class="mt-2 h-28 w-28 hover:cursor-pointer "
                                        src="{{ asset('assets/image.png') }}" alt="">
                                </div>


                            </div>
                        </div>

                        <p class="border-t-2 border-[#bebebe] m-0 w-100"></p>

                        <div class="flex justify-center py-5">
                            <button
                                class="bg-[#f7aa10] inline-block mt-0 text-neutral-900 px-6 py-2.5 rounded-xl font-bold uppercase text-[13px]">Monitize
                                Design </button>
                        </div>
                    </div>
                    {{-- Form End --}}

                    {{-- Upload Buttons --}}

                    <div x-show="uploadModal" x-transition:enter.duration.500ms x-cloak="display:none"
                        class="justify-between py-[102px]">
                        <div class="flex justify-between w-full md:flex md:space-x-28 md:pt-16">

                            <div class="grid justify-center ">
                                <p class="text-center md:hidden l">Upload To Design Stack</p>

                                <label for="design_stack">
                                    <input class="hidden" type="file" accept="image/*" name="design_stack"
                                        id="design_stack">
                                    <div class="relative w-full group">
                                        <div
                                            class="relative z-40 flex items-center justify-center w-40 h-40 mx-auto transition-all duration-500 bg-white cursor-pointer group-hover:translate-x-8 group-hover:shadow-2xl group-hover:-translate-y-8 rounded-xl">

                                            <img class="w-40 h-40 rounded-xl"
                                                src="{{ asset('assets/uploadDesignStack.png') }}" id="designImage"
                                                alt="">
                                        </div>
                                        <div
                                            class="absolute inset-0 z-30 flex items-center justify-center w-40 h-40 mx-auto transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="justify-center ">
                                <p class="text-center md:hidden">Upload To Printable Stack</p>

                                <label for="printable_stack">
                                    <input class="hidden" type="file" accept="*" name="printable_stack"
                                        id="printable_stack">

                                    <div class="relative w-full group">
                                        <div class="relative z-40 flex items-center justify-center w-40 h-40 mx-auto transition-all duration-500 bg-white cursor-pointer group-hover:translate-x-8 group-hover:shadow-2xl group-hover:-translate-y-8 rounded-xl"
                                            id="printable-contaner">
                                            <img class="w-40 h-40 rounded-xl"
                                                src="{{ asset('assets/uploadPrintableStack.png') }}" id="printImage"
                                                alt="">
                                        </div>
                                        <div
                                            class="absolute inset-0 z-30 flex items-center justify-center w-40 h-40 mx-auto transition-all duration-300 bg-transparent border border-gray-200 border-dashed opacity-0 group-hover:opacity-80 rounded-xl">
                                        </div>
                                    </div>
                                </label>

                            </div>
                        </div>
                        <div class="justify-center p-0 my-5 text-sm font-extrabold md:flex gap-52"
                            x-cloak="display:none">
                            <p class="text-center ">Upload To Design Stack</p>

                            <p class="ml-12">Upload To Printable Stack</p>

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
<script>
    let designFileInput = document.getElementById('design_stack')
    const designImage = document.getElementById('designImage');
    designFileInput.addEventListener('change', function() {
        const file = designFileInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(e) {
                designImage.src = e.target.result;
            }
        }
    })

    let printFileInput = document.getElementById('printable_stack')
    const printImage = document.getElementById('printImage');

    printFileInput.addEventListener('change', function() {
        const file = printFileInput.files[0];
        const extension = file.name.split('.').pop();
        console.log(extension);
        if (file && (extension == 'png' || extension == 'jpeg' || extension == 'jpg')) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(e) {
                printImage.src = e.target.result;
                printImage.style.padding = "0px";
            }
        }
        if (extension != 'png' || extension != 'jpeg' || extension != 'jpg') {
            printImage.style.padding = "20px";
            printImage.src = "{{ asset('assets/file.png') }}";

        }
    })
</script>

</div>
