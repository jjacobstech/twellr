@props(['product'])

    <div class=" rounded-xl  ">
        <img class=" rounded-xl h-full w-full md:h-20 md:w-28 lg:h-32 lg:w-40 aspect-square"
            src='{{ asset("uploads/products/design-stack/$product->front_view") }}' alt="">
    </div>


