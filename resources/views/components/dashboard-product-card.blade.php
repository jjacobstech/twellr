<!-- Create this as components/product-card.blade.php -->
@props(['purchase'])

    <div class="overflow-hidden rounded-xl relative duration-150 transition-all  group hover:shadow-lg bg-black">
        <div class="absolute flex justify-between w-full p-3">
            <span
                class="text-xs sm:text-sm md:text-base font-bold text-golden bg-black  px-2 py-1 sm:px-3  rounded-full top-2 right-2 bg-opacity-80">
                {{ $purchase->product->name }}
            </span>
            <span
                class="text-xs sm:text-sm md:text-base font-bold text-golden bg-black  px-2 py-1 sm:px-3  rounded-full top-2 right-2 bg-opacity-80">
                {{ App\Models\AdminSetting::first()->value('currency_symbol') . $purchase->product->price }}
            </span>
        </div>

          <div class="absolute justify-between w-full p-3 bottom-1 hidden group-hover:block group-hover:ease-in-out duration-100">
            <span
                class="text-[10px] sm:text-xs md:text-sm font-bold text-golden bg-black  px-2 py-1 sm:px-3  rounded-full top-2 right-2 bg-opacity-80">
             {{ $purchase->created_at->diffForHumans() }}
            </span>
        </div>


        <img class="w-full h-48 rounded-xl object-cover " loading="lazy"
            src="{{ asset('uploads/products/design-stack/' . $purchase->product->front_view) }}"
            alt="{{ $purchase->product->name }} product image">



    </div>
