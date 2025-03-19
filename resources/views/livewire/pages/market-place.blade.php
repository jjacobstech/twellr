<?php

use App\Models\Product;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public $products;
    public $productCategory = '';
    public $productFilter = '';

    public function mount()
    {
        $this->productCategory = request()->slug;
        $this->productFilter = request()->filter;
        $this->products = Product::all();
    }
}; ?>

<div x-data="{
    form: true,
    uploadModal: false,
    backButton: false,

}">

    <div class="flex gap-1 w-[100%]">
        <x-market-place-sidebar class="" />
        <div class="relative bg-white w-screen md:w-[80%] h-screen  pb-20">
            <div
                class="relative grid w-full h-full  gap-5 px-5 overflow-y-scroll grid-col-6 md:grid-cols-4  sm:grid-cols-2 py-5 ">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach

            </div>
        </div>

    </div>
