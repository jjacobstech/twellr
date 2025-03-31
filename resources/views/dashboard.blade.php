    <?php
    use App\Models\Product;
    auth()->user()->role == 'creative' ? ($latestProducts = Product::latest()->take(5)->get()) : ($latestProducts = Product::latest()->take(6)->get());
    ?>
    <x-app-layout>

        <div class="flex m-0 md:w-full  md:h-screen">
            @if (Auth::user()->isCreative())
                <x-creative-sidebar />
            @endif
            @if (route('dashboard') == url()->current())
                <div
                    class="grid space-y-5 md:space-y-3 lg:space-y-7 w-screen px-3 md:pt-5 lg:mx-1 pb-5 md:pb-0  bg-white md:flex md:flex-col md:flex-1 md:h-full md:w-72 md:overflow-auto">
                    <div class="relative flex">
                        <img class="h-[200] md:h-[254px] w-full rounded-xl" src="{{ asset('assets/sales.png') }}"
                            alt="">
                    </div>

                    <div>
                        @if (Auth::user()->isCreative())
                            <div class="relative grid w-full gap-3 lg:hidden md:grid-cols-3 sm:grid-cols-2">
                                @foreach ($latestProducts as $latestProduct)
                                    <div>
                                        <div class=" rounded-xl">
                                            <img class="w-full h-48 rounded-xl"
                                                src="{{ asset('uploads/products/design-stack/' . $latestProduct->front_view) }}"
                                                alt="">
                                        </div>

                                        <div class="mt-2 text">
                                            <p class="h3 text-golden"> {{ $latestProduct->price }} </p>
                                            <p class="font-bold text-gray-500 p"> {{ $latestProduct->category }} </p>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="relative lg:grid w-full gap-3 hidden lg:grid-cols-5">
                                @foreach ($latestProducts as $latestProduct)
                                    <div>
                                        <div class=" rounded-xl">
                                            <img class="w-full h-48 rounded-xl"
                                                src="{{ asset('uploads/products/design-stack/' . $latestProduct->front_view) }}"
                                                alt="">
                                        </div>

                                        <div class="mt-2 text">
                                            <p class="h3 text-golden"> {{ $latestProduct->price }} </p>
                                            <p class="font-bold text-gray-500 p"> {{ $latestProduct->category }} </p>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (!Auth::user()->isCreative())
                            <div class="relative grid w-full gap-5  lg:hidden md:grid-cols-6 sm:grid-cols-4 ">
                                @foreach ($latestProducts as $latestProduct)
                                    <div>
                                        <div class=" rounded-xl">
                                            <img class="w-full h-48 rounded-xl"
                                                src="{{ asset('uploads/products/design-stack/' . $latestProduct->front_view) }}"
                                                alt="">
                                        </div>

                                        <div class="mt-3 text">
                                            <p class="h3 text-golden"> {{ $latestProduct->price }} </p>
                                            <p class="font-bold text-gray-500 p"> {{ $latestProduct->category }} </p>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="relative hidden lg:flex w-full gap-5  lg:grid-col-6">
                                @foreach ($latestProducts as $latestProduct)
                                    <div>
                                        <div class=" rounded-xl">
                                            <img class="w-full h-48 rounded-xl"
                                                src="{{ asset('uploads/products/design-stack/' . $latestProduct->front_view) }}"
                                                alt="">
                                        </div>

                                        <div class="mt-3 text">
                                            <p class="h3 text-golden"> {{ $latestProduct->price }} </p>
                                            <p class="font-bold text-gray-500 p"> {{ $latestProduct->category }} </p>

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
