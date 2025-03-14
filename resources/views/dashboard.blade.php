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
                        <div class="relative grid w-full px-4 gap-7 y-52 grid-col-6 md:grid-cols-4 sm:grid-cols-2">
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />

                        </div>
                    @endif

                    @if (!Auth::user()->isCreative())
                        <div class="relative grid w-full gap-10 px-4 grid-col-6 md:grid-cols-6 sm:grid-cols-4 ">
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />
                            <x-product-card wire:navigate />


                        </div>
                    @endif
                </div>
            </div>
        @endif
        @yield('content')
    </div>
</x-app-layout>
