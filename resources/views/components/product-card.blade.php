<a href="#">
    <div class="card md:my-4 my-4">
        <div class=" rounded-xl">
            <img class="w-full rounded-xl h-[235px]"
                src="{{ asset('uploads/products/design-stack/' . $product->design_stack) }}" alt="">
        </div>

        <div class="text mt-3">
            <p class="h3 text-golden"> {{ $product->price }} </p>
            <p class="p text-gray-500 font-bold"> {{ $product->category }} </p>

        </div>
    </div>
</a>
