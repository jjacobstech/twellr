  <div class="my-4 card" :key="{{ $product->id }}">
      <div class=" rounded-xl">
          <img class="w-full rounded-xl h-[235px]"
              src="{{ asset('uploads/products/design-stack/' . $product->front_view) }}" alt="">
      </div>

      <div class="flex justify-between mt-3 text-lg font-extrabold">
          <div>
              <p class="text-lg text-golden"> {{ $product->price }} </p>
              <p class="text-sm font-medium text-gray-500"> {{ $product->name }} </p>
          </div>
          <div>
              <x-bladewind.button wire:click="orderModal({{ $product->id }})" size="small" radius="medium"
                  type="bg-black hover:bg-navy-blue focus:ring-0" button_text_css="text-white ">
                  Buy
              </x-bladewind.button>
          </div>

      </div>
  </div>
