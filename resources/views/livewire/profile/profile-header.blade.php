<?php

use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Contest;
use App\Models\Product;
use App\Models\Referral;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

new class extends Component {
    use Toast;

    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public int $rating;
    public ?string $referral_link = '';
    public $referral;
    public $points;
    public $discount;
    public $total_referrals = 0;
    public $total_rewards = 0;
    public $recent_referrals;
    public $designs = null;
    public $rating_status = '';
    public $statuses = [
        '0' => 'Newbie',
        '1' => 'Rookie',
        '2' => 'Notched',
        '3' => 'Dazzler',
        '4' => 'Boss',
        '5' => 'Jagaban',
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->firstname = Auth::user()->firstname;
        $this->lastname = Auth::user()->lastname;
        $this->email = Auth::user()->email;
        $this->referral_link = config('app.url') . '/r/' . Auth::user()->referral_link;
        $this->rating = Auth::user()->rating;
        $this->designs = $this->designs = Product::where('user_id', Auth::id())->get();
        $this->discount = Auth::user()->discount;
        // Get total referrals count
        $this->total_referrals = Referral::where('referrer_id', Auth::id())->count();

        // Get total rewards/points
        $this->total_rewards = Referral::where('referrer_id', Auth::user()->id)->sum('reward_points') ?? 0;

        // Get recent referrals (limited to 5)
        $this->recent_referrals = Referral::where('referrer_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($referral) {
                $referred = User::where('id', $referral->referred_id)->first();
                return [
                    'name' => $referred->firstname . ' ' . $referred->lastname,
                    'avatar' => $referred->avatar,
                    'created_at' => $referred->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Copy referral link to clipboard
     */
    public function copyReferralLink()
    {
        $this->dispatch('referral-copied');
    }

    public function submit($id)
    {
        $product = Product::with('category')->find($id); // Get a single product
        if ($product) {
            $exist = Contest::where('user_id', Auth::id())->where('product_id', $product->id)->where('category_id', $product->category->id)->first();

            if ($exist) {
                $exist->delete();
                $this->success('Contest Entry Cancelled');
            } else {
                $contest = Contest::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'category_id' => $product->category->id,
                    'type' => 'design_fest',
                ]);

                if ($contest) {
                    $this->success('Contest Entry Successful');
                } else {
                    $this->error('Contest Entry Failed', 'Unable to enter Design Fest');
                }
            }
        } else {
            $this->error('Product Not Found', 'Invalid product ID');
        }
    }
};
?>
<div x-data='{
copyReferralLink(){
        const copyText = document.getElementById("referral_link");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(copyText.value).then(() => {
            // Visual feedback
            const copyButton = document.getElementById("copyButton");
            const originalText = copyButton.innerText;
            const originalBg = copyButton.classList.contains("bg-golden");

            copyButton.innerText = "Copied!";
            copyButton.classList.remove("bg-golden");
            copyButton.classList.add("bg-green-500");
            copyButton.classList.add("text-white");

            setTimeout(() => {
                copyButton.innerText = originalText;
                copyButton.classList.remove("bg-green-500");
                copyButton.classList.remove("text-white");
                if (originalBg) copyButton.classList.add("bg-golden");
            }, 2000);
        });
    }
}'
    class="w-full overflow-y-scroll mb-20 scrollbar-none
           px-2 sm:px-4 md:px-8 lg:px-12 xl:px-16">

<div class="hidden">
    <!-- Header -->
    <header class="flex items-center justify-between w-full">
        <h2
            class="py-2 sm:py-3 md:py-4
                 text-xl sm:text-2xl md:text-3xl lg:text-4xl
                 font-extrabold text-gray-500">
            {{ __('Profile') }}
        </h2>

        <a href="{{ route('settings') }}">
            <svg class="text-gray-500
                      h-5 w-5 sm:h-6 sm:w-6 md:h-7 md:w-7"
                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </a>
    </header>

    <!-- Profile Cover Section -->
    <div class="w-full">
        <div style="background-image: url('@if (empty(Auth::user()->cover)) {{ asset('assets/pexels-solliefoto-298863.jpg') }}@else{{ asset('uploads/cover/' . Auth::user()->cover) }} @endif')"
            class="rounded-[14px] bg-no-repeat bg-cover bg-center justify-center items-center w-full
                   mt-4 sm:mt-8 md:mt-12 lg:mt-16 xl:mt-20
                   min-h-[180px] sm:min-h-[200px] md:min-h-[220px] lg:min-h-[250px] xl:min-h-[280px] pt-1">

            <div class="relative flex justify-center w-full">
                <img class="absolute z-10 object-cover {{ !empty(Auth::user()->avatar) && str_contains(Auth::user()->avatar, 'https://') ? 'aspect-square' : '' }}
                         w-16 h-16 mt-10
                         sm:w-18 sm:h-18 sm:mt-12
                         md:w-20 md:h-20 md:mt-16
                         lg:w-32 lg:h-32 lg:mt-20
                         xl:w-40 xl:h-40 xl:mt-28
                         rounded-full border-2 sm:border-3 md:border-4 border-white shadow-lg"
                    src="@if (!empty(Auth::user()->avatar) && str_contains(Auth::user()->avatar, 'https://')) {{ Auth::user()->avatar }}@elseif(!empty(Auth::user()->avatar)){{ asset('uploads/avatar/' . Auth::user()->avatar) }}@else{{ asset('assets/icons-user.png') }} @endif"
                    alt="{{ Auth::user()->name }}" />
            </div>

            <div
                class="w-full bg-white h-full rounded-[14px] border-0 text-center items-center justify-center grid
                      mt-20 px-4
                      sm:mt-20 sm:py-6 sm:px-6
                      md:mt-24 md:py-8 md:px-8
                      lg:mt-28 lg:py-9 lg:px-10
                      xl:mt-48 xl:py-9 xl:px-12">

                @if (Auth::user()->isCreative())
                    <div
                        class="grid items-center justify-center
                                sm:mt-8 md:mt-10 lg:mt-12 xl:mt-16 space-y-3">
                        <x-bladewind.rating rating="{{ $rating }}" size="small"
                            class="text-golden text-sm sm:text-base md:text-lg lg:text-xl" name="creative-rating" />

                        <span
                            class="inline-flex justify-center text-center text-white rounded-full bg-navy-blue font-semibold
                                   px-2 py-1 text-xs leading-4
                                   sm:px-3 sm:text-sm sm:leading-5
                                   md:px-3 md:text-sm md:leading-5
                                   lg:px-4 lg:text-base lg:leading-6">
                            @switch($rating)
                                @case(1)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(2)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(3)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(4)
                                    {{ $statuses[$rating] }}
                                @break

                                @case(5)
                                    {{ $statuses[$rating] }}
                                @break

                                @default
                                    {{ $statuses[$rating] }}
                            @endswitch
                        </span>
                    </div>
                    <h1
                        class="font-bold text-gray-500 leading-tight break-words px-2
                             my-2 text-sm sm:text-base md:text-lg lg:text-2xl xl:text-3xl">
                        {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                    </h1>
                @else
                    <h1
                        class="font-bold text-gray-500 leading-tight break-words px-2
                             my-10 text-sm
                             sm:my-10 sm:text-base
                             md:my-12 md:text-lg
                             lg:my-14 lg:text-2xl
                             xl:my-16 xl:text-3xl">
                        {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                    </h1>
                @endif
            </div>
        </div>

        <!-- Enhanced Referral Section -->
        <div
            class="bg-white shadow-sm rounded-xl w-full my-6 sm:my-8 md:my-10
                  px-3 py-6 sm:px-4 sm:py-8 md:px-6 md:py-10 lg:px-8 lg:py-10 xl:px-10 xl:py-10
                  md:flex md:justify-between md:gap-4 lg:gap-6">

            <!-- Left Column: Header and Stats -->
            <div class="w-full md:w-1/2 grid justify-center md:justify-items-start gap-2">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="text-golden mr-2
                                w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h1
                        class="font-extrabold text-gray-700
                             text-lg sm:text-xl md:text-2xl lg:text-3xl">
                        Refer and Earn
                    </h1>
                </div>
                <p
                    class="font-bold text-gray-500 mb-4 w-full text-center md:text-left
                         text-base sm:text-lg md:text-xl">
                    Share your link with friends and earn rewards
                </p>

                <!-- Stats Cards -->
                <div class="grid w-full grid-cols-2 gap-3 sm:gap-4 mt-2">
                    <div class="p-3 sm:p-4 text-center bg-white rounded-lg shadow-sm">
                        <p class="text-xs sm:text-sm text-gray-500">Total Referrals</p>
                        <p
                            class="text-navy-blue font-bold
                                 text-lg sm:text-xl md:text-2xl">
                            {{ $total_referrals ?? '0' }}
                        </p>
                    </div>
                    <div class="p-3 sm:p-4 text-center bg-white rounded-lg shadow-sm">
                        <p class="text-xs sm:text-sm text-gray-500">Rewards Earned</p>
                        <p
                            class="text-golden font-bold
                                 text-lg sm:text-xl md:text-2xl">
                            {{ $total_rewards ?? '0' }} pts
                        </p>
                    </div>
                </div>

                <div class="grid w-full grid-cols-2 gap-3 sm:gap-4 mt-2">
                    <div class="p-3 sm:p-4 text-center bg-white rounded-lg shadow-sm">
                        <p class="text-xs sm:text-sm text-gray-500">Discount</p>
                        <p
                            class="text-navy-blue font-bold
                                 text-lg sm:text-xl md:text-2xl">
                            {{ $discount }}%
                        </p>
                    </div>
                </div>

                <!-- How It Works Section -->
                <div class="w-full p-3 sm:p-4 mt-4 sm:mt-6 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-2 font-bold text-gray-700
                             text-sm sm:text-base">
                        How It Works
                    </h3>
                    <ol
                        class="pl-4 sm:pl-5 space-y-1 list-decimal text-gray-600
                             text-xs sm:text-sm">
                        <li>Share your unique referral link with friends</li>
                        <li>Friends sign up using your link</li>
                        <li>You both earn rewards when they make their first purchase</li>
                    </ol>
                </div>
            </div>

            <!-- Right Column: Referral Link and Share Options -->
            <div class="w-full md:w-1/2 mt-6 md:mt-0">
                <p class="mb-2 font-bold text-gray-700
                         text-base sm:text-lg">
                    Your unique referral link
                </p>
                <div class="flex w-full mb-4">
                    <input
                        class="w-full text-gray-500 bg-white border-0 rounded-l-lg shadow-inner
                                 px-3 py-2 sm:px-4 sm:py-3
                                 text-xs sm:text-sm md:text-base"
                        readonly type="text" value="{{ $referral_link }}" id="referral_link">
                    <button id="copyButton"
                        class="font-bold text-gray-700 transition-all duration-200 rounded-r-lg bg-golden hover:bg-navy-blue hover:text-white focus:outline-none
                               px-4 py-2 sm:px-6 sm:py-3
                               text-xs sm:text-sm md:text-base"
                        type="button" @click="copyReferralLink()">
                        Copy
                    </button>
                </div>

                <!-- Social Sharing Options -->
                <p class="mb-2 font-bold text-gray-700
                         text-base sm:text-lg">
                    Share via
                </p>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referral_link) }}"
                        target="_blank"
                        class="flex items-center text-white transition-colors duration-75 bg-blue-600 rounded-lg hover:bg-white hover:text-blue-600
                               px-3 py-2 sm:px-4 sm:py-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 320 512">
                            <path
                                d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" />
                        </svg>
                    </a>
                    <a href="https://x.com/intent/tweet?text={{ urlencode('Join me on Twellr! Use my referral link:') }}&url={{ urlencode($referral_link) }}"
                        target="_blank"
                        class="flex items-center text-white transition-colors bg-black rounded-lg hover:bg-white hover:text-black
                               px-3 py-2 sm:px-4 sm:py-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 fill-current" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512">
                            <path
                                d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm297.1 84L257.3 234.6 379.4 396H283.8L209 298.1 123.3 396H75.8l111-126.9L69.7 116h98l67.7 89.5L313.6 116h47.5zM323.3 367.6L153.4 142.9H125.1L296.9 367.6h26.3z" />
                        </svg>
                    </a>
                    <a href="https://wa.me/?text={{ urlencode('Join me on Twellr! Use my referral link: ' . $referral_link) }}"
                        target="_blank"
                        class="flex items-center text-white transition-colors bg-green-500 rounded-lg hover:bg-white hover:text-green-600
                               px-3 py-2 sm:px-4 sm:py-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512">
                            <path
                                d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                        </svg>
                    </a>
                </div>

                <!-- Referral Stats Details -->
                <div class="p-3 sm:p-4 mt-4 sm:mt-6 bg-white rounded-lg shadow-sm">
                    <h3 class="mb-2 font-bold text-gray-700
                             text-sm sm:text-base">
                        Recent Referrals
                    </h3>
                    <div class="overflow-y-auto max-h-32">
                        @if (isset($recent_referrals) && count($recent_referrals) > 0)
                            @foreach ($recent_referrals as $referral)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <div class="flex items-center gap-2 sm:gap-3">
                                        @if (!isset($referral['avatar']) && $referral['avatar'] == null)
                                            <div
                                                class="flex items-center justify-center text-white rounded-full bg-navy-blue
                                                      w-8 h-8 sm:w-10 sm:h-10
                                                      text-xs sm:text-sm">
                                                {{ substr($referral['name'] ?? 'User ', 0, 1) }}
                                            </div>
                                        @else
                                            <img class="object-cover rounded-full
                                                      w-8 h-8 sm:w-10 sm:h-10"
                                                src="{{ asset('uploads/avatar/' . ($referral['avatar'] ?? 'default-avatar.png')) }}"
                                                alt="User Avatar">
                                        @endif
                                        <span
                                            class="text-gray-700
                                                   text-xs sm:text-sm md:text-base">
                                            {{ $referral['name'] }}
                                        </span>
                                    </div>
                                    <span class="text-gray-500
                                               text-xs">
                                        {{ $referral['created_at'] ?? 'Recently' }}
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <p class="py-2 text-gray-500
                                    text-xs sm:text-sm">
                                No referrals yet. Share your link to start earning!
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Designs Section (Creative Users Only) -->
    @if (Auth::user()->isCreative())
        <div
            class="w-full bg-white shadow-sm rounded-xl my-6 sm:my-8 md:my-10
                  py-6 sm:py-8 md:py-10
                  px-0 sm:px-4 md:px-6 lg:px-8 xl:px-10">

            <!-- Header -->
            <header class="flex items-center justify-between w-full mb-4 px-4 sm:px-0">
                <h2
                    class="font-extrabold text-gray-500
                         text-xl sm:text-2xl md:text-3xl lg:text-4xl">
                    {{ __('Designs') }}
                </h2>
            </header>

            <!-- Responsive Grid -->
            <div
                class="relative bg-white w-full overflow-y-scroll scrollbar-none
                      pb-2 md:pb-0
                      md:w-3/4 lg:w-full
                      md:h-screen lg:py-4">

                <!-- Mobile/Tablet Grid -->
                <div x-cloak="display:hidden"
                    class="relative grid justify-center w-full gap-3 sm:gap-4 md:gap-5 mb-16 lg:hidden
                           grid-cols-2 sm:grid-cols-3 md:grid-cols-2
                           px-4 sm:px-0
                           md:h-screen">
                    @forelse ($designs as $design)
                        <x-design-card :$design />
                    @empty
                        <div
                            class="text-center text-gray-500 col-span-full
                                   text-sm sm:text-base">
                            No Designs Available
                        </div>
                    @endforelse
                </div>

                <!-- Desktop Grid -->
                <div x-cloak="display:hidden"
                    class="relative hidden w-full gap-3 sm:gap-4 md:gap-5 mb-16 lg:grid
                           lg:grid-cols-4 xl:grid-cols-5
                           px-4 sm:px-5
                           py-1">
                    @forelse ($designs as $design)
                        <x-design-card :$design />
                    @empty
                        <div
                            class="text-center text-gray-500 col-span-full
                                   text-sm sm:text-base">
                            No Designs Available
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>

      <!-- Comment Section (Creative Users Only) -->
    <section class=" z-50 inset-0 bg-black/40  rounded-lg border-2 border-purple-600 p-4 my-8 mx-auto max-w-xl">
        <h3 class="font-os text-lg font-bold">Comments</h3>

        <!-- Sample Comment 1 -->
        <div class="flex mt-4">
            <div class="w-14 h-14 rounded-full bg-purple-400/50 flex-shrink-0 flex items-center justify-center">
                <img class="h-12 w-12 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/43.jpg"
                    alt="">
            </div>

            <div class="ml-3">
                <div class="font-medium text-purple-800">John Doe</div>
                <div class="text-gray-600">Posted on 2023-10-02 14:30</div>
                <div class="mt-2 text-purple-800">This is a sample comment. Lorem ipsum dolor sit amet, consectetur
                    adipiscing elit.
                </div>
            </div>
        </div>

        <!-- Sample Comment 2 -->
        <div class="flex mt-4">
            <div class="w-14 h-14 rounded-full bg-purple-400/50 flex-shrink-0 flex items-center justify-center">
                <img class="h-12 w-12 rounded-full object-cover"
                    src="https://randomuser.me/api/portraits/women/43.jpg" alt="">
            </div>
            <div class="ml-3">
                <div class="font-medium text-purple-800">Jane Smith</div>
                <div class="text-gray-600">Posted on 2023-10-02 15:15</div>
                <div class="mt-2 text-purple-800">Another sample comment. Sed quis velit auctor, bibendum dolor in,
                    accumsan tellus.
                </div>
            </div>
        </div>

        <!-- Comment Form -->
        <form class="mt-4">
            <div class="mb-4">
                <label for="name" class="block text-purple-800 font-medium">Name</label>
                <input type="text" id="name" name="name"
                    class="border-2 border-purple-600 p-2 w-full rounded" required>
            </div>

            <div class="mb-4">
                <label for="comment" class="block text-purple-800 font-medium">Comment</label>
                <textarea id="comment" name="comment" class="border-2 border-purple-600 p-2 w-full rounded" required></textarea>
            </div>

            <button type="submit"
                class="bg-purple-700 text-white font-medium py-2 px-4 rounded hover:bg-purple-600">Post
                Comment
            </button>
        </form>
    </section>
</div>
