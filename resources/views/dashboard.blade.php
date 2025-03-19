    <?php
    use App\Models\Product;
    auth()->user()->role == 'creative' ? ($latestProducts = Product::latest()->take(5)->get()) : ($latestProducts = Product::latest()->take(6)->get());
    ?>
    <x-app-layout>

        <div class="flex m-0 md:w-full h-100 ">
            @if (auth()->user()->role == 'creative')
                <x-creative-sidebar />
            @endif
            @if (route('dashboard') == url()->current())
                <div
                    class="grid w-screen h-screen px-2 pb-32 mx-1 overflow-y-scroll bg-white md:flex md:flex-col md:flex-1 md:h-full md:w-72 md:overflow-auto">
                    <div class="relative flex-1 p-4 md:h-7">
                        <img class="h-[200] md:h-[254px] w-full rounded-xl" src="{{ asset('assets/sales.png') }}"
                            alt="">
                    </div>

                    <div class="pt-50">
                        @if (Auth::user()->isCreative())
                            <div class="relative grid w-full px-4 gap-7 y-52 grid-col-6 md:grid-cols-5 sm:grid-cols-2">
                                @foreach ($latestProducts as $latestProduct)
                                    <div class="">
                                        <div class=" rounded-xl">
                                            <img class="w-full rounded-xl h-48"
                                                src="{{ asset('uploads/products/design-stack/' . $latestProduct->design_stack) }}"
                                                alt="">
                                        </div>

                                        <div class="text mt-2">
                                            <p class="h3 text-golden"> {{ $latestProduct->price }} </p>
                                            <p class="p text-gray-500 font-bold"> {{ $latestProduct->category }} </p>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (!Auth::user()->isCreative())
                            <div class="relative grid w-full gap-10 px-4 grid-col-6 md:grid-cols-6 sm:grid-cols-4 ">
                                @foreach ($latestProducts as $latestProduct)
                                    <div class="card md:my-4 my-4">
                                        <div class=" rounded-xl">
                                            <img class="w-full rounded-xl h-[235px]"
                                                src="{{ asset('storage/products/' . $latestProduct->design_stack) }}"
                                                alt="">
                                        </div>

                                        <div class="text mt-3">
                                            <p class="h3 text-golden"> {{ $latestProduct->price }} </p>
                                            <p class="p text-gray-500 font-bold"> {{ $latestProduct->category }} </p>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            @yield('content')
        </div>
    </x-app-layout>
