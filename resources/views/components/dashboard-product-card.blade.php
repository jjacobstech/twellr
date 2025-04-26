<!-- Create this as components/product-card.blade.php -->
@props(['product'])

<div class="product-card">
    <div class="overflow-hidden rounded-xl relative">

        <img class="w-full h-48 rounded-xl object-cover transition-transform hover:scale-105"
            src="{{ asset('uploads/products/design-stack/' . $product->front_view) }}"
            alt="{{ $product->category }} product image">
    </div>

    <div class="mt-2">


        <p class="text-lg font-semibold text-golden">{{App\Models\AdminSetting::first()->value('currency_symbol').$product->price }}</p>
        <p class="font-bold text-gray-500">{{ $product->category }}</p>
    </div>
</div>
