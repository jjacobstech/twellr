<?php

use App\Models\User;
use App\Models\Referral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public int $rating;
    public ?string $referral_link = '';
    public $referral;
    public $points;
    public $total_referrals = 0;
    public $total_rewards = 0;
    public $recent_referrals;

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

        // Get total referrals count
        $this->total_referrals = Referral::where('referrer_id', Auth::id())->count();

        // Get total rewards/points
        $this->total_rewards = Referral::where('referrer_id', Auth::user()->id)->count() ?? 0;

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
};
?>
<div class="w-[100%] overflow-y-scroll px-8 md:px-16 mb-20 scrollbar-none">
    <header class="flex items-center justify-between w-full">
        <h2 class="py-4 text-4xl font-extrabold text-gray-500">
            {{ __('Profile') }}
        </h2>

        <a href="{{ route('settings') }}">
            <svg class="h-7 w-7 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </a>
    </header>
    <div class="w-full">
        <div style="background-image: url('@if (empty(Auth::user()->cover)) {{ asset('assets/pexels-solliefoto-298863.jpg') }}@else{{ asset('uploads/cover/' . Auth::user()->cover) }} @endif')"
            class="rounded-[14px] bg-no-repeat bg-cover justify-center items-center w-full min-h-[250px]">
            <div class="relative flex justify-center w-full">
                <img class="w-20 h-20 md:w-40 md:h-40 rounded-full absolute md:mt-28 z-10 mt-14 border-[#bebebe] border-[1px] object-cover"
                    src="@if (empty(Auth::user()->avatar)) {{ asset('assets/icons-user.png') }}
                        @else
                        {{ asset('uploads/avatar/' . Auth::user()->avatar) }} @endif"
                    alt="{{ Auth::user()->name }}" />
            </div>
            <div
                class="mt-24 md:py-9 w-100 bg-white rounded-[14px] md:mt-[12rem] text-center items-center justify-center grid">
                <span class="mt-12 md:mt-16">
                    <x-bladewind.rating rating="{{ $rating }}" size="medium" class="text-golden"
                        name="creative-rating" />
                </span>
                <h1 class="my-2 text-lg font-bold text-gray-500 md:text-3xl">{{ Auth::user()->firstname }}
                    {{ Auth::user()->lastname }}</h1>
            </div>
        </div>

        <!-- Enhanced Referral Section -->
        <div class="justify-between px-5 py-10 my-10 bg-white md:px-10 md:gap-6 md:flex w-100 rounded-xl shadow-sm">
            <!-- Left Column: Header and Stats -->
            <div class="grid justify-center gap-2 w-100 md:w-1/2 md:justify-items-start">
                <div class="flex items-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-golden mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h1 class="text-3xl font-extrabold text-gray-700">Refer and Earn</h1>
                </div>
                <p class="text-xl font-bold text-gray-500 md:text-left w-100 mb-4">Share your link with friends and earn
                    rewards</p>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 gap-4 w-full mt-2">
                    <div class="bg-white rounded-lg p-4 shadow-sm text-center">
                        <p class="text-sm text-gray-500">Total Referrals</p>
                        <p class="text-2xl font-bold text-navy-blue">{{ $total_referrals ?? '0' }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm text-center">
                        <p class="text-sm text-gray-500">Rewards Earned</p>
                        <p class="text-2xl font-bold text-golden">{{ $total_rewards ?? '0' }} pts</p>
                    </div>
                </div>

                <!-- How It Works Section -->
                <div class="mt-6 bg-white rounded-lg p-4 shadow-sm w-full">
                    <h3 class="font-bold text-gray-700 mb-2">How It Works</h3>
                    <ol class="text-sm text-gray-600 list-decimal pl-5 space-y-1">
                        <li>Share your unique referral link with friends</li>
                        <li>Friends sign up using your link</li>
                        <li>You both earn rewards when they make their first purchase</li>
                    </ol>
                </div>
            </div>

            <!-- Right Column: Referral Link and Share Options -->
            <div class="md:w-1/2 w-100 mt-6 md:mt-0">
                <p class="mb-2 text-lg font-bold text-gray-700">Your unique referral link</p>
                <div class="flex w-full mb-4">
                    <input class="rounded-l-lg border-0 px-4 py-3 w-full text-gray-500 shadow-inner bg-white" readonly
                        type="text" value="{{ $referral_link }}" id="referral_link">
                    <button id="copyButton"
                        class="px-6 py-3 font-bold bg-golden text-gray-700 rounded-r-lg hover:bg-navy-blue hover:text-white transition-all duration-200 focus:outline-none"
                        type="button" onclick="copyReferralLink()">
                        Copy
                    </button>
                </div>

                <!-- Social Sharing Options -->
                <p class="text-lg font-bold text-gray-700 mb-2">Share via</p>
                <div class="flex flex-wrap gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referral_link) }}"
                        target="_blank"
                        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 320 512">
                            <path
                                d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" />
                        </svg>
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode('Join me on Twellr! Use my referral link:') }}&url={{ urlencode($referral_link) }}"
                        target="_blank"
                        class="flex items-center px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512">
                            <path
                                d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z" />
                        </svg>
                        Twitter
                    </a>
                    <a href="https://wa.me/?text={{ urlencode('Join me on Twellr! Use my referral link: ' . $referral_link) }}"
                        target="_blank"
                        class="flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 448 512">
                            <path
                                d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                        </svg>
                        WhatsApp
                    </a>
                </div>

                <!-- Referral Stats Details -->
                <div class="mt-6 bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-bold text-gray-700 mb-2">Recent Referrals</h3>
                    <div class="max-h-32 overflow-y-auto">
                        @if (isset($recent_referrals) && count($recent_referrals) > 0)
                            @foreach ($recent_referrals as $referral)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <div class="flex items-center gap-3">
                                        @if (!isset($referral['avatar']) && $referral['avatar'] == null)
                                            <div
                                                class="w-10 h-10 flex items-center justify-center rounded-full bg-navy-blue text-white ">
                                                {{ substr($referral['name'] ?? 'User ', 0, 1) }}
                                            </div>
                                        @else
                                            <img class="w-10 h-10 rounded-full object-cover"
                                                src="{{ asset('uploads/avatar/' . ($referral['avatar'] ?? 'default-avatar.png')) }}"
                                                alt="User  Avatar">
                                        @endif
                                        <span class="text-gray-700">{{ $referral['name'] }}</span>
                                    </div>
                                    <span
                                        class="text-xs text-gray-500">{{ $referral['created_at'] ?? 'Recently' }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-500 text-sm py-2">No referrals yet. Share your link to start earning!
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyReferralLink() {
        const copyText = document.getElementById("referral_link");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(copyText.value).then(() => {
            // Visual feedback
            const copyButton = document.getElementById("copyButton");
            const originalText = copyButton.innerText;
            const originalBg = copyButton.classList.contains('bg-golden');

            copyButton.innerText = "Copied!";
            copyButton.classList.remove('bg-golden');
            copyButton.classList.add('bg-green-500');
            copyButton.classList.add('text-white');

            setTimeout(() => {
                copyButton.innerText = originalText;
                copyButton.classList.remove('bg-green-500');
                copyButton.classList.remove('text-white');
                if (originalBg) copyButton.classList.add('bg-golden');
            }, 2000);
        });
    }
</script>
