<?php
use App\Models\Review;
use function Livewire\Volt\state;

state([
    'reviews' => [],
]);

?>

<section class="py-12 lg:py-20 bg-gradient-to-br from-gray-50 rounded-2xl via-blue-50 to-purple-50 relative overflow-hidden">

    <!-- Decorative background elements -->
    <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
    <div class="absolute top-10 left-10 w-20 h-20 bg-gradient-to-r from-blue-400 to-purple-400 rounded-full opacity-10 animate-pulse"></div>
    <div class="absolute bottom-20 right-20 w-16 h-16 bg-gradient-to-r from-pink-400 to-yellow-400 rounded-full opacity-10 animate-bounce"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Header -->
        <div class="text-center mb-12 lg:mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-gray-900 via-blue-900 to-purple-900 bg-clip-text text-transparent mb-4">
                Customer Reviews
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Discover what our amazing clients have to say about their experience
            </p>
        </div>

        <!-- Reviews Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">
            @forelse ($reviews as $index => $review)
                <div class="group cursor-pointer transform transition-all duration-500 hover:-translate-y-2 hover:scale-105"
                     style="animation-delay: {{ $index * 0.1 }}s">

                    <!-- Gradient border wrapper -->
                    <div class="relative p-[2px] rounded-2xl bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="bg-white rounded-2xl h-full"></div>
                    </div>

                    <!-- Card content -->
                    <div class="relative bg-white/80 backdrop-blur-sm border border-white/20 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-500 -mt-full">

                        <!-- User info -->
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="relative flex-shrink-0">
                                @if (!empty($review->user->avatar) && str_contains($review->user->avatar, 'https://'))
                                    <img  src="{{ $review->user->avatar }}"
                                         alt="{{ $review->user->firstname }}"
                                         class="w-12 h-12 rounded-full object-cover ring-2 ring-white shadow-lg">
                                @else
                                    @if ($review->user->avatar)
                                        <img src="{{ asset('uploads/avatar/' . $review->user->avatar) }}"
                                             alt="{{ $review->user->firstname }}"
                                             class="w-12 h-12 rounded-full object-cover ring-2 ring-white shadow-lg">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
                                            {{ strtoupper(substr($review->user->firstname, 0, 1) . substr($review->user->lastname, 0, 1)) }}
                                        </div>
                                    @endif
                                @endif


                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-gray-900 truncate">
                                    {{ $review->user->firstname . ' ' . explode(' ', $review->user->lastname)[0] }}
                                </h3>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm px-2 py-1 rounded-full font-medium
                                        {{ $review->user->role === 'creative' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $review->user->role === "creative" ? "Designer" : "Customer" }}
                                    </span>
                                </div>
                            </div>


                        </div>

                        <!-- Review text -->
                        <blockquote class="text-gray-700 leading-relaxed mb-4 italic">
                            "{{ $review->review }}"
                        </blockquote>

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <span class="text-sm text-gray-500">
                                {{ $review->created_at->format('M j, Y') }}
                            </span>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Reviews Yet</h3>
                        <a href="{{ route('login') }}" class="text-gray-600 underline hover:text-black">Be the first to share your experience with us!</a>
                    </div>
                </div>
            @endforelse
        </div>


    </div>
</section>


