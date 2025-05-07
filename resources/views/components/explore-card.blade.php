@props(['product'])

    <div class=" rounded-xl shadow-md">
        <img class=" rounded-xl h-full w-full md:h-20 md:w-28 lg:h-32 lg:w-40 aspect-square hover:scale-110 transition duration-150 ease-in-out"
            src='{{ asset("uploads/products/design-stack/$product->front_view") }}' alt="">
    </div>


