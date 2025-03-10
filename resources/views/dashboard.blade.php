<x-app-layout>
    <div class="flex m-0 md:w-full h-100 ">
        @if (auth()->user()->role == 'creative')
            <x-creative-sidebar />
        @endif
        @if (route('dashboard') == url()->current())
            <div class="flex h-full w-72 mx-1 flex-1 flex-col  bg-white px-2">
                <div class="relative h-7 flex-1 p-4">
                    <img class="h-[254px] w-full rounded-xl" src="{{ asset('assets/sales.png') }}" alt="">
                </div>
                <div class="grid  w-full gap-10 px-4  grid-col-6 md:grid-cols-4 sm:grid-cols-2 relative ">
                    <x-product-card wire:navigate />
                    <x-product-card wire:navigate />
                    <x-product-card wire:navigate />
                    <x-product-card wire:navigate />

                    {{-- @if ($projects->isEmpty())
                        <div class="flex justify-center ">
                            <h1 class='w-full mt-40 ml-10 text-5xl font-bold text-center'>No Project</h1>
                        </div>
                    @else
                        @foreach ($projects as $project)
                            <x-product-card wire:navigate />
                        @endforeach
                    @endif --}}
                </div>

            </div>
        @endif
        @yield('content')
    </div>
</x-app-layout>
