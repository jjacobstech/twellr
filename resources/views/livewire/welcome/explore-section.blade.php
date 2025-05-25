<?php

use App\Models\Product;
use App\Models\Contest;
use App\Models\ContestWinner;
use function Livewire\Volt\{state, layout};
// Sections array
state([
    'sections' => [
        (object) [
            'title' => 'Latest Designs',
            'path' => 'market.place',
            'filter' => 'latest-design',
            'items' => Product::latest()->take(7)->get(),
        ],
        (object) [
            'title' => 'Who Rocked It Best',
            'path' => 'design.contest',
            'filter' => 'who-rocked-it-best',
            'items' => Contest::where('type', 'who_rocked_it_best')->latest()->take(7)->get(),
            'isPhoto' => true,
        ],
        (object) [
            'title' => 'Trending Designs',
            'path' => 'market.place',
            'filter' => 'trending-designs',
            'items' => Product::daily()->latest()->take(7)->get(),
        ],
        (object) [
            'title' => 'Designers Of The Week',
            'path' => 'market.place',
            'filter' => 'designers-of-the-week',
            'items' => (ContestWinner::winner() != null) ? ContestWinner::winner()->take(7) : [],
        ],
        (object) [
            'title' => 'Featured Designs',
            'path' => 'market.place',
            'filter' => 'featured-desgns',
            'items' => Product::inRandomOrder()->take(7)->get(),
        ],
    ],
]);
?>

<div class="flex w-full gap-1">
    <div class="w-full px-4 py-8 pb-20 overflow-y-scroll bg-white sm:px-8 scrollbar-none">
        <h1 class="text-4xl font-extrabold text-gray-500 md:text-center">Explore</h1>

        @foreach ($sections as $section)
            <div class="flex items-center justify-between w-full mt-5 text-lg">
                <p class="font-extrabold text-navy-blue text-[21px]">{{ $section->title }}</p>
                <a href="{{ url(route($section->path, ['filter' => $section->filter])) }}"
                    class="flex items-center font-extrabold text-golden">
                    See all
                    <svg class="w-[14px] h-[14px] ml-1 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="#fbaa0d"
                            d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224H32c-17.7 0-32 14.3-32 32s14.3 32 32 32h306.7L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                    </svg>
                </a>
            </div>

            <div
                class="grid grid-cols-2 gap-3 p-3 bg-gray-100 shadow-md sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 rounded-2xl ">
                @forelse ($section->items as $item)
                    @if (!empty($section->isPhoto))
                        <div class="shadow-md rounded-xl">
                            <img src="{{ asset('uploads/contest/' . $item->photo) }}" alt=""
                                class="object-cover w-full h-full transition duration-150 ease-in-out rounded-xl aspect-square md:h-20 shadow-md md:w-28 lg:h-32 lg:w-40 hover:scale-110" />
                        </div>
                    @else
                        <x-explore-card wire:navigate :product="$item" />
                    @endif
                @empty
                    <p class="text-center text-gray-400 col-span-full">No Designs</p>
                @endforelse
            </div>
        @endforeach
    </div>
</div>
