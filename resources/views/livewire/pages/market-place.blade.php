<?php

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Rate;
use App\Models\User;
use App\Models\State;
use App\Models\Rating;
use App\Models\Review;
use Mary\Traits\Toast;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Product;
use App\Models\Discount;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\ShippingFee;
use App\Models\Transaction;
use App\Models\AdminSetting;
use App\Models\Notification;
use Livewire\Volt\Component;
use App\Models\ContestWinner;
use App\Models\PlatformEarning;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, layout, mount, uses, with};

layout('layouts.app');
uses(Toast::class);
state([
    'modal' => false,
    'marketplace' => true,
    'checkout' => false,
    'order' => [],
    'address' => auth()->user()->address,
    'size' => '',
    'owner' => false,
    'added' => null,
    'exists' => null,
    'materials' => fn() => Material::all(),
    'material' => '',
    'totalPrice' => 0,
    'shipping_fee' => 0,
    'quantity' => 1,
    'subTotal' => 0,
    'itemTotal' => 0,
    'newPrice' => 0,
    'oldPrice' => 0,
    'useCurrentDiscount' => 'unUsed',
    'phone_no' => auth()->user()->phone_no,
    'discount' => auth()->user()->discount,
    'referral_link' => '',
    'commentInput' => '',
    'reviewInput' => '',
    'comments' => [],
    'review' => [],
    'shipping_rates' => fn() => ShippingFee::all(),
    'locations' => fn() => State::all(),
    'location' => '',
    'filterTerm' => fn() => session('filter'),
    'designer' => fn() => session('user'),
    'starRating' => 0,
    'discountAmount' => 0,
    'minimum_rating' => fn() => AdminSetting::first()->value('minimum_rating'),
]);
state([
    'categories' => [
        (object) [
            'title' => 'Trending Designs',
            'filter' => 'trending-designs',
        ],
        (object) [
            'title' => 'Latest Designs',
            'filter' => 'latest-designs',
        ],
        (object) [
            'title' => 'Featured Designs',
            'filter' => 'featured-designs',
        ],
        (object) [
            'title' => 'Best Selling Designs',
            'filter' => 'best-selling',
        ],
        (object) [
            'title' => 'Designers Of The Week',
            'filter' => 'designers-of-the-week',
        ],
    ],
]);

with(function () {
    if ($this->filterTerm === 'trending-designs') {
        return [
            'products' => Product::daily()
                ->whereHas('designer', function ($query) {
                    $query->where('state_id', 'like', "%{$this->location}%");
                })
                ->with('category', 'designer')
                ->get(),
        ];
    } elseif ($this->filterTerm === 'designers-of-the-week') {
        return ['products' => ContestWinner::winner()];
    } elseif ($this->filterTerm === 'featured-designs') {
        return ['products' => Product::inRandomOrder()->get()];
    } elseif ($this->filterTerm === 'latest-designs') {
        return [
            'products' => Product::orderBy('created_at', 'desc')
                ->whereHas('designer', function ($query) {
                    $query->where('state_id', 'like', "%{$this->location}%");
                })
                ->with('category', 'designer')
                ->get(),
        ];
    } elseif ($this->filterTerm === 'best-selling') {
        return [
            'products' => Product::mostPurchased()
                ->whereHas('designer', function ($query) {
                    $query->where('state_id', 'like', "%{$this->location}%");
                })
                ->with('category', 'designer')
                ->get(),
        ];
    } elseif ($this->filterTerm === 'picked-for-you') {
        return [
            'products' => Auth::user()
                ?->pickedForYous()
                ->whereHas('designer', function ($query) {
                    $query->where('state_id', 'like', "%{$this->location}%");
                })
                ->with('category', 'designer')
                ->get(),
        ];
    } elseif (session('designer')) {
        return [
            'products' => Product::orderByDesc('created_at')
                ->whereHas('designer', function ($query) {
                    $query->where('referral_link', 'like', $this->designer);
                })
                ->with('category', 'designer')
                ->get(),
        ];
    } else {
        return [
            'products' => Product::orderByDesc('created_at')
                ->whereHas('designer', function ($query) {
                    $query->where('state_id', 'like', "%{$this->location}%");
                })
                ->with('category', 'designer')
                ->get(),
        ];
    }
});

mount(function () {
    if (request()->has('filter')) {
        $filter = request('filter');
        session(['filter' => $filter]);

        // Redirect to same page without the query parameter
        redirect()->route('market.place');
    }
});

$setRating = function ($id) {
    $designer = $this->order->designer->id;

    $exist = Rating::where('user_id', '=', Auth::id())->where('designer_id', '=', $designer)->where('product_id', '=', $this->order->id)->exists();

    if ($exist) {
        $rating = Rating::where('user_id', '=', Auth::id())->where('designer_id', '=', $designer)->where('product_id', '=', $this->order->id)->first();
        $rating->rate = $id;
        $rating->save();
        $this->starRating = $id;
        $this->success('Rating Successful', 'Your rating has been submitted');
        return;
    }

    $rating = Rating::create([
        'user_id' => Auth::id(),
        'designer_id' => $designer,
        'product_id' => $this->order->id,
        'rate' => $id,
    ]);

    $ratingsQuery = Rating::where('designer_id', $designer);
    $count = $ratingsQuery->count();
    $average = $ratingsQuery->avg('rate');
    $globalAverage = Rating::avg('rate');

    $minRatings = $this->minimum_rating;

    $bayesianAverage = ($count / ($count + $minRatings)) * $average + ($minRatings / ($count + $minRatings)) * $globalAverage;

    User::where('id', $designer)->update(['rating' => $bayesianAverage]);

    if ($rating) {
        $this->starRating = $id;
        $this->success('Rating Successful', 'Your rating has been submitted');
        return;
    }
};

$filter = function ($filter = '') {
    if ($filter === '') {
        session()->flash('filter');
    }
    return $this->filterTerm = $filter;
};

$locate = function ($location = '') {
    if ($location === '') {
        session()->flash('filter');
    }
    return $this->location = $location;
};

$incrementQuantity = function () {
    $this->useDiscount('false');
    $this->useCurrentDiscount = 'unUsed';
    $this->quantity++;
    $this->subTotal = $this->itemTotal * $this->quantity;
    $this->totalPrice = $this->subTotal + $this->shipping_fee;
};

$decrementQuantity = function () {
    $this->useDiscount('false');
    $this->useCurrentDiscount = 'unUsed';
    $this->subTotal = $this->subTotal - $this->subTotal / $this->quantity;
    $this->totalPrice = $this->subTotal + $this->shipping_fee;
    $this->quantity == 1 ? '' : $this->quantity--;
};

$useDiscount = function ($discount = '') {
    if ($discount === 'true') {
        $this->discountAmount = $this->totalPrice * ($this->discount / 100);
        $this->totalPrice = $this->totalPrice * (1 - $this->discount / 100);
        $this->useCurrentDiscount = 'used';
    } elseif ($discount === 'false') {
        $this->discountAmount = 0;
        $this->totalPrice = 1 - $this->discount / 100 === 0 ? $this->totalPrice : $this->totalPrice / (1 - $this->discount / 100);
        $this->useCurrentDiscount = 'unUsed';
    }
};

$updateShippingFee = function () {
    $this->useDiscount('false');
    $this->useCurrentDiscount = 'unUsed';
    if ($this->location == 0) {
        $this->shipping_fee = 0;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
    } else {
        $shipping = ShippingFee::where('id', '=', $this->location)->first();
        $this->shipping_fee = $shipping->rate;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
    }
};

$addMaterial = function ($id = 0) {
    $this->useDiscount('false');
    $this->useCurrentDiscount = 'unUsed';
    $material = Material::where('id', '=', $id)->first();

    if ($material) {
        $price = $material->price;
        $this->newPrice = $price;
        $this->itemTotal = $this->itemTotal - $this->oldPrice;
        // Removes the old price
        $this->oldPrice = $this->newPrice;
        $this->itemTotal = $this->itemTotal + $this->newPrice;
        // Adds the new price
        $this->subTotal = $this->itemTotal * $this->quantity;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
    } else {
        $price = 0;
        $this->newPrice = $price;
        $this->itemTotal = $this->itemTotal - $this->oldPrice;
        // Removes the old price
        $this->oldPrice = $this->newPrice;
        $this->itemTotal = $this->itemTotal + $this->newPrice;
        // Adds the new price
        $this->subTotal = $this->itemTotal * $this->quantity;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
    }
};

$addToCart = function ($id) {
    $product = Product::where('id', '=', $id)->first();

    $itemExists = Cart::where('product_id', '=', $product->id)->where('user_id', '=', Auth::id())->exists();
    if ($itemExists) {
        $this->warning("Cannot Add $product->name", 'This item is already in the cart');
    } elseif (!$itemExists) {
        $addToCart = Cart::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);
        if (!$addToCart) {
            abort(500, 'Something went wrong but we are working on it');
        }
        $this->success('Added', "$product->name added to cart!");
    }
};

$commentDesign = function ($id) {
    $data = (object) $this->validate(['commentInput' => ['required', 'string']]);
    $comment = Comment::create([
        'user_id' => Auth::id(),
        'product_id' => $id,
        'content' => $data->commentInput,
        'is_approved' => 1,
    ]);

    if (!$comment) {
        $this->error('Comment Error', 'Something happened but we are working on it');
    } else {
        $this->success('Comment Successful');
        $this->mount();
    }
    return $this->comments = Comment::where('product_id', '=', $id)->with('user')->get();
};

$reviewDesign = function ($id) {
    $data = (object) $this->validate(['reviewInput' => ['required', 'string']]);
    $review = Review::create([
        'user_id' => Auth::id(),
        'product_id' => $id,
        'review' => $data->reviewInput,
    ]);

    if (!$review) {
        $this->error('Review Error', 'Something happened but we are working on it');
    } else {
        $this->success('Review Successful');
        $this->mount();
    }
    return $this->review = Review::where('product_id', '=', $id)->where('user_id', '=', Auth::id())->first();
};

$generateReferenceNumber = function () {
    $prefix = 'TRX';
    do {
        $timestamp = now()->format('YmdHis');
        $randomString = strtoupper(Str::random(6));
        $reference_no = $prefix . $timestamp . $randomString . Auth::id();

        $exists = Transaction::where('ref_no', '=', $reference_no)->first();
    } while ($exists);

    return $reference_no;
};

$orderModal = function ($id) {
    $this->order = Product::where('id', '=', $id)->with('designer')->first();
    $orderRating = Rating::where('user_id', '=', Auth::id())
        ->where('designer_id', '=', $this->order->designer->id)
        ->where('product_id', '=', $this->order->id)
        ->first();
    $this->starRating = $orderRating ? $orderRating->rate : 0;
    $this->comments = Comment::where('product_id', '=', $id)->with('user')->get();
    $this->review = Review::where('product_id', '=', $id)->where('user_id', '=', Auth::id())->first();
    $this->referral_link = config('app.url') . '/r/' . $this->order->designer->referral_link;
    $this->order->user_id == Auth::id() ? ($this->owner = true) : ($this->owner = false);
    $this->modal = true;
};

$orderProduct = function () {
    $this->marketplace = false;
    $this->checkout = true;
    $this->itemTotal = $this->order->price;
    $this->subTotal = $this->itemTotal * $this->quantity;
    $this->totalPrice = $this->subTotal + $this->shipping_fee;
};

$closeOrderProduct = function () {
    $this->marketplace = true;
    $this->checkout = false;
    $this->itemTotal = 0;
    $this->subTotal = 0;
    $this->totalPrice = 0;
    $this->shipping_fee = 0;
};

$completeCheckout = function () {
    if (empty(Auth::user()->phone_no)) {
        session()->put('was_redirected', true);
        $this->redirectIntended(route('settings'), true);
    } else {
        $orderInfo = (object) $this->validate([
            'size' => ['required'],
            'material' => ['required', 'not_in:0'],
            'address' => ['required', 'min:10'],
            'phone_no' => ['required', 'min:10'],
            'location' => ['required', 'not_in:0'],
        ]);

        if ($this->totalPrice > Auth::user()->wallet_balance) {
            return $this->error("Low Balance $" . Auth::user()->wallet_balance, 'Your wallet balance is low. Add funds to continue');
        } else {
            try {
                DB::beginTransaction();

                $ref_no = $this->generateReferenceNumber();
                $buyer = User::find(Auth::id());
                $creative = User::find($this->order->user_id);

                $transaction = Transaction::create([
                    'user_id' => $this->order->user_id,
                    'buyer_id' => $buyer->id,
                    'amount' => $this->order->price * $this->quantity,
                    'transaction_type' => 'sales',
                    'ref_no' => $ref_no,
                    'status' => 'pending',
                ]);

                $purchase = Purchase::create([
                    'transactions_id' => $transaction->id,
                    'user_id' => $this->order->user_id,
                    'buyer_id' => $buyer->id,
                    'product_id' => $this->order->id,
                    'delivery_status' => 'pending',
                    'phone_no' => $orderInfo->phone_no,
                    'address' => $orderInfo->address,
                    'amount' => $this->totalPrice,
                    'location_id' => $this->location,
                    'size' => $orderInfo->size,
                    'product_name' => $this->order->name,
                    'product_category' => $this->order->category->name,
                    'material_id' => $orderInfo->material,
                    'quantity' => $this->quantity,
                    'discounted' => true,
                ]);

                if ($this->useCurrentDiscount === 'used') {
                    $discount = Discount::create([
                        'user_id' => $buyer->id,
                        'product_id' => $this->order->id,
                        'transaction_id' => $transaction->id,
                        'purchase_id' => $purchase->id,
                        'amount' => $this->discount,
                        'ref_no' => $ref_no,
                    ]);

                    if (!$discount) {
                        throw new \Exception('Failed to apply discount.');
                    }
                    $discounted = $buyer->discount = 0;
                    $discounted = $buyer->save();
                    if (!$discounted) {
                        throw new \Exception('Failed to deduct discount.');
                    }
                }

                $material = Material::find($this->material);
                if (!$material) {
                    throw new \Exception('Selected material not found.');
                }

                if ($this->useCurrentDiscount === 'used') {
                    $platformEarning = PlatformEarning::create([
                        'purchase_id' => $purchase->id,
                        'transaction_id' => $transaction->id,
                        'quantity' => $this->quantity,
                        'price' => $material->price,
                        'total' => $this->totalPrice - ($this->order->price * $this->quantity + $this->shipping_fee), //This deducts shipping fee and designer price and gives the real discounted price
                        'fee_type' => 'material sales',
                        'notes' => 'discounted purchase',
                    ]);
                } else {
                    $platformEarning = PlatformEarning::create([
                        'purchase_id' => $purchase->id,
                        'transaction_id' => $transaction->id,
                        'quantity' => $this->quantity,
                        'price' => $material->price,
                        'total' => $material->price * $this->quantity,
                        'fee_type' => 'material sales',
                    ]);
                }

                // Deduct from buyer
                $deducted = $buyer->decrement('wallet_balance', $this->totalPrice);

                if (!$deducted) {
                    throw new \Exception('Buyer balance deduction failed.');
                }

                // Credit creative
                $deposited = $creative->increment('wallet_balance', $this->order->price * $this->quantity);
                if (!$deposited) {
                    throw new \Exception('Creative wallet update failed.');
                }

                // Notify creative
                Notification::create([
                    'user_id' => $creative->id,
                    'title' => "{$this->order->name} design has been ordered",
                    'message' => "{$buyer->firstname} {$buyer->lastname} has purchased {$this->order->name}",
                    'type' => 'sales',
                ]);

                // Shipping transaction
                $shippingTransaction = Transaction::create([
                    'buyer_id' => $buyer->id,
                    'amount' => $this->shipping_fee,
                    'ref_no' => $ref_no,
                    'transaction_type' => 'shipping_fee',
                    'status' => 'successful',
                ]);

                PlatformEarning::create([
                    'quantity' => 1,
                    'purchase_id' => $purchase->id,
                    'transaction_id' => $shippingTransaction->id,
                    'price' => $this->shipping_fee,
                    'total' => $this->shipping_fee,
                    'fee_type' => 'shipping fee',
                ]);

                DB::commit();

                $this->success('Order Successful', 'Your order is on its way', redirectTo: route('market.place'));
            } catch (\Exception $e) {
                DB::rollBack();
                $this->withdrawalModal = false;
                $this->error('Transaction Error', $e->getMessage());
            }
        }
    }
};
?>

<div class="w-screen h-screen scrollbar-thin scrollbar-track-white scrollbar-thumb-navy-blue">

    @error('material')
        {{ $this->warning('Material Not Selected') }}
    @enderror

    @error('size')
        {{ $this->warning('Size Not Selected') }}
    @enderror

    @error('phone_no')
        {{ $this->warning('Mobile Number Is Required For Delivery', 'Please add your mobile number') }}
    @enderror

    @error('address')
        {{ $this->warning('Address Not Selected') }}
    @enderror

    @error('location')
        {{ $this->warning('Location Not Selected') }}
    @enderror

    <div wire:loading
        class="py-3 mb-6 z-50 text-white transition-opacity duration-500 border rounded alert-info alert top-40 md:top-5 right-1 bg-navy-blue border-navy-blue absolute"
        role="alert">
        <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        Loading . . .
    </div>

    <!-- Marketplace -->
    <div wire:show="marketplace" class="grid w-screen h-full lg:space-x-1 lg:flex">

        <x-market-place-sidebar :locations="$locations" :categories="$categories" :filterTerm="$filterTerm" />
        <div
            class="relative bg-white w-screen pb-16 md:pb-0 md:w-[100%] lg:w-[80%] md:h-screen  lg:py-4 overflow-y-scroll scrollbar-none mb-20">

            <div>

                <div x-cloak="display:hidden"
                    class="relative grid justify-center w-screen grid-cols-2 gap-5 px-5 mt-16 mb-16 sm:grid-cols-3 lg:hidden md:grid-cols-4">
                    @if ($products && $products->count())
                        @foreach ($products as $product)
                            <x-product-card :$product />
                        @endforeach
                    @else
                        <div class="py-10 mt-10 text-lg font-medium text-center text-gray-500 col-span-full uppercase">
                            No designs available.
                        </div>
                    @endif
                </div>

                <div x-cloak="display:hidden"
                    class="relative hidden w-full  gap-5 px-5 py-1  lg:grid lg:grid-cols-4 mb-[70px]">
                    @if ($products && $products->count())
                        @foreach ($products as $product)
                            <x-product-card :$product />
                        @endforeach
                    @else
                        <div class="py-10 text-lg font-medium text-center text-gray-500 col-span-full uppercase">
                            No designs available.
                        </div>
                    @endif

                </div>

                <div x-data="{ isOpen: @entangle('modal').live }" x-show="isOpen" x-cloak='display:none' x-transition.opacity
                    class="fixed inset-0 z-50 w-screen h-full px-5 py-16 sm:py-12 md:py-12 md:px-20 lg:py-12 bg-black/40 backdrop-blur-sm pb-26">
                    <div class="grid h-full lg:flex justify-evenly rounded-xl md:flex-row"
                        @click.away="$wire.modal = false">
                        @if ($order)
                            <div
                                class="object-fit-contain lg:w-[75%] carousel rounded-t-xl md:rounded-none lg:rounded-l-xl">
                                <div class="w-full h-64 overflow-y-auto bg-gray-100 lg:h-full scrollbar-none carousel">
                                    <div class="w-full h-full carousel">
                                        <!-- FRONT VIEW -->
                                        <div class="relative w-full h-full bg-black carousel-item over" id="front-view">
                                            <img src="@if ($order) {{ asset('uploads/products/design-stack/' . $order->front_view) }} @endif"
                                                alt="product front view"
                                                class="w-full h-full object-contain aspect-[4/3]">
                                            <div
                                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                                <a href="#side-view"
                                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                                <a href="#back-view"
                                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                            </div>
                                        </div>

                                        <!-- BACK VIEW -->
                                        <div class="relative w-full h-full bg-black carousel-item" id="back-view">
                                            <img src="@if ($order) {{ asset('uploads/products/design-stack/' . $order->back_view) }} @endif"
                                                alt="product back view"
                                                class="w-full h-full object-contain aspect-[4/3]">
                                            <div
                                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                                <a href="#front-view"
                                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                                <a href="#side-view"
                                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                            </div>
                                        </div>

                                        <!-- SIDE VIEW -->
                                        <div class="relative w-full h-full bg-black carousel-item" id="side-view">
                                            <img src="@if ($order) {{ asset('uploads/products/design-stack/' . $order->side_view) }} @endif"
                                                alt="product side view"
                                                class="w-full h-full object-contain aspect-[4/3]">
                                            <div
                                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                                <a href="#back-view"
                                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                                <a href="#front-view"
                                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="lg:w-[25%] px-6 md:px-10 lg:px-6 bg-white lg:rounded-r-xl space-y-3 overflow-y-scroll py-5 scrollbar-none">
                                <!-- design info -->

                                <div class="flex flex-wrap ">

                                    <h1 class="flex-auto text-xl font-semibold text-black">

                                        {{ $order->name }}

                                    </h1>
                                    <div class="text-xl font-semibold text-black">

                                        {{ App\Models\AdminSetting::first()->value('currency_symbol') . $order->price }}

                                    </div>
                                    <div class="flex-none w-full mt-2 font-extrabold text-black text-md ">

                                        {{ $order->category->name }}

                                    </div>
                                </div>

                                <!-- Order Button -->
                                <div class="flex justify-between mb-4 text-sm font-medium">

                                    @if ($owner)
                                        <x-mary-button disabled label="Order"
                                            class="bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                            wire:click="orderProduct" spinner />
                                    @else
                                        <x-mary-button label="Order"
                                            class="bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                            wire:click="orderProduct" spinner />
                                    @endif

                                    <div class="grid">
                                        {{-- To use this component to set ratings for designers setRatings($id) function has to be creates with  where $id is the rating number , check component for more ... --}}
                                        <x-bladewind.rating rating="{{ $starRating }}" functional size="small"
                                            class="text-golden" name="creative-rating" />
                                    </div>



                                </div>

                                <!-- Social Sharing Options -->
                                <p class="mb-2 text-lg font-bold text-gray-700">Share</p>
                                <div class="flex flex-wrap gap-3">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referral_link) }}"
                                        target="_blank"
                                        class="flex items-center px-4 py-2 text-white transition-colors duration-75 bg-blue-600 rounded-lg hover:bg-white hover:text-blue-600 ">
                                        <svg class="w-5 h-5 " fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 320 512">
                                            <path
                                                d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" />
                                        </svg>
                                    </a>
                                    <a href="https://x.com/intent/tweet?text={{ urlencode('Join me on Twellr! Use my referral link:') }}&url={{ urlencode($referral_link) }}"
                                        target="_blank"
                                        class="flex items-center px-4 py-2 text-white transition-colors bg-black rounded-lg hover:bg-white hover:text-black">
                                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 448 512">
                                            <path
                                                d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm297.1 84L257.3 234.6 379.4 396H283.8L209 298.1 123.3 396H75.8l111-126.9L69.7 116h98l67.7 89.5L313.6 116h47.5zM323.3 367.6L153.4 142.9H125.1L296.9 367.6h26.3z" />
                                        </svg>

                                    </a>
                                    <a href="https://wa.me/?text={{ urlencode('Join me on Twellr! Use my referral link: ' . $referral_link) }}"
                                        target="_blank"
                                        class="flex items-center px-4 py-2 text-white transition-colors bg-green-500 rounded-lg hover:bg-white hover:text-green-600">
                                        <svg class="w-5 h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 448 512">
                                            <path
                                                d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                                        </svg>

                                    </a>
                                </div>

                                @if (is_null($review) || $review === [])
                                    <!-- Review Section -->
                                    <div class="flex flex-wrap gap-3">
                                        <div class="grid w-full">
                                            <x-text-input id="reviewInput" placeholder="Drop Review" :class="$errors->get('reviewInput')
                                                ? 'block w-full mt-2 ring-1 border-0 ring-red-600 text-white bg-[#bebebe] rounded-xl'
                                                : 'block border-0 text-white w-full mt-2 bg-[#bebebe] rounded-xl'"
                                                wire:model="reviewInput" type="text" name="reviewInput" autofocus
                                                autocomplete="reviewInput" />
                                            <x-input-error class="absolute pt-1 mt-12" :messages="$errors->get('reviewInput') ? 'Comment Cannot Be Empty' : ''" />
                                        </div>
                                        <x-mary-button
                                            class="bg-[#001f54] text-white hover:bg-golden hover:border-golden mt-2"
                                            wire:click="reviewDesign({{ $order->id }})" spinner>
                                            @svg('eva-message-circle', ['class' => 'w-5 h-5'])
                                            Submit
                                        </x-mary-button>

                                    </div>
                                @else
                                    <p class="text-black scrollbar-none">Review Submitted</p>
                                @endif

                                <!-- Comment Section -->

                                @if (auth()->user()->isFollowing($order->designer->id))
                                    <div class="flex flex-wrap gap-3">
                                        <div class="grid w-full">
                                            <x-text-input id="commentInput" placeholder="Write a Comment"
                                                :class="$errors->get('commentInput')
                                                    ? 'block w-full mt-2 ring-1 border-0 ring-red-600 text-white bg-[#bebebe] rounded-xl'
                                                    : 'block border-0 text-white w-full mt-2 bg-[#bebebe] rounded-xl'" wire:model="commentInput" type="text"
                                                name="commentInput" autofocus autocomplete="commentInput" />
                                            <x-input-error class="absolute pt-1 mt-12" :messages="$errors->get('commentInput') ? 'Comment Cannot Be Empty' : ''" />
                                        </div>
                                        <x-mary-button
                                            class="bg-[#001f54] text-white hover:bg-golden hover:border-golden mt-2"
                                            wire:click="commentDesign({{ $order->id }})" spinner>
                                            @svg('eva-message-circle', ['class' => 'w-5 h-5'])
                                            Comment
                                        </x-mary-button>

                                    </div>


                                    @forelse ($comments as $comment)
                                        <div class="grid">

                                            <div
                                                class="h-full scrollbar-thin scrollbar-track-white scrollbar-thumb-navy-blue">
                                                <div class="chat chat-start">
                                                    <div class="chat-image avatar">
                                                        <div class="w-10 rounded-full">
                                                            @if (!empty($comment->user->avatar) && str_contains($comment->user->avatar, 'https://'))
                                                                <x-bladewind.avatar
                                                                    class="ring-0 border-0 aspect-square"
                                                                    size="medium" :image="$comment->user->avatar" />
                                                            @else
                                                                @if ($comment->user->avatar)
                                                                    <x-bladewind.avatar class="ring-0  border-0 "
                                                                        size="medium"
                                                                        image="{{ asset('uploads/avatar/' . $comment->user->avatar) }}" />
                                                                @else
                                                                    <x-bladewind.avatar class="ring-0 border-0"
                                                                        size="medium"
                                                                        image="{{ asset('assets/icons-user.png') }}" />
                                                                @endif
                                                            @endif

                                                        </div>
                                                    </div>
                                                    <div class="chat-header">
                                                        <span
                                                            class="text-gray-500">{{ $comment->user->firstname . ' ' . $comment->user->lastname }}</span>
                                                        <time class="text-xs opacity-50">
                                                            {{ Carbon::parse($comment->created_at)->format('D-M-Y') }}</time>
                                                    </div>
                                                    <div class="chat-bubble"> {{ $comment->content }}</div>
                                                </div>

                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-black scrollbar-none">No Comments</p>
                                    @endforelse
                                @else
                                    <p class="text-black scrollbar-none">Follow
                                        {{ $order->designer->firstname . ' ' . $order->designer->lastname }} to be able
                                        to
                                        Comment </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end">
                        <span wire:click='modal = false' class="absolute cursor-pointer right-5 top-3">
                            @svg('heroicon-o-x-mark', ['class' => 'justify btn-dark hover:bg-red-500 bg-golden hover:text-white border-navy-blue text-white btn-sm btn-circle'])
                        </span>
                    </div>
                </div>


            </div>

        </div>


    </div>



    <!-- Checkout -->
    <div wire:show="checkout" x-cloak x-transition:enter="transition ease-in-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in-out duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="w-full h-screen overflow-y-scroll bg-gray-100 pb-36 scrollbar-thin scrollbar-track-white scrollbar-thumb-navy-blue">
        <!-- Main Container -->
        <div class="px-4 py-4 mx-auto max-w-20xl sm:px-6 lg:px-4 md:flex md:gap-3">

            <!-- Checkout Container -->
            @if ($order)
                <!-- Checkout Items Section -->
                <div class="w-full overflow-hidden bg-white rounded-l-lg shadow-md lg:w-2/3">
                    <div class="p-6 border-b border-navy-blue bg-navy-blue">
                        <h2 class="text-xl font-semibold text-white">Checkout</h2>
                    </div>

                    <!-- Checkout Items -->
                    <div class="">
                        <div class="p-4 sm:p-6">

                            <!-- Mobile View (Optimized) -->
                            <div class="flex flex-col p-4 bg-white rounded-lg shadow sm:hidden">
                                <!-- Product Header with Image and Basic Info -->
                                <div class="flex mb-5">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0 w-24 h-24">
                                        <img class="object-cover w-full h-full rounded-md shadow-sm"
                                            src="{{ asset('uploads/products/design-stack/' . $order->front_view) }}"
                                            alt="{{ $order->name }}">
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-grow ml-4">
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $order->name }}</h3>
                                        <p class="mt-1 text-base font-medium text-gray-700">
                                            ${{ $order->price }}</p>
                                    </div>
                                </div>
                                <!-- Product Options Section -->
                                <div class="space-y-4">
                                    <!-- Material Selection -->
                                    <div class="w-full">
                                        <x-input-label class="mb-1 font-bold text-gray-700" for="material"
                                            :value="__('Material')" />
                                        <select wire:model="material"
                                            x-on:change='$wire.addMaterial(event.target.value)' id="status"
                                            class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                            <option value="0">Select a material
                                            </option>
                                            @forelse ($materials as $material)
                                                <option value="{{ $material->id }}">
                                                    {{ ucfirst($material->name) }} - ${{ $material->price }}</option>
                                            @empty
                                                <option value="">No materials available</option>
                                            @endforelse
                                        </select>
                                        <x-input-error :messages="$errors->get('material') ? 'Material Not Selected' : ''" class="mt-1" />
                                    </div>

                                    <!-- Size Selection -->
                                    <div class="w-full">
                                        <x-input-label class="mb-1 font-bold text-gray-700" for="size"
                                            :value="__('Size')" />
                                        <select id="size" required wire:model="size"
                                            class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                            <option value="">
                                                Select Size
                                            </option>
                                            @foreach (['xs', 's', 'm', 'l', 'xl'] as $size)
                                                <option class="uppercase" value="{{ $size }}">
                                                    {{ strtoupper($size) }}</option>
                                            @endforeach


                                        </select>

                                        <x-input-error :messages="$errors->get('size') ? 'Size Not Selected' : ''" class="mt-1" />
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="w-full mt-2">
                                        <x-input-label class="mb-1 font-bold text-gray-700" for="quantity"
                                            :value="__('Quantity')" />
                                        <div
                                            class="flex items-center justify-between overflow-hidden border border-gray-300 rounded-lg">
                                            <button wire:click="decrementQuantity"
                                                class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 transition">
                                                <svg class="w-4 h-4 text-gray-600" fill="currentColor"
                                                    viewBox="0 0 448 512">
                                                    <path
                                                        d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                </svg>
                                            </button>
                                            <span class="flex-grow px-4 py-2 font-medium text-center text-gray-800"
                                                wire:text="quantity"></span>
                                            <button wire:click="incrementQuantity"
                                                class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 transition">
                                                <svg class="w-4 h-4 text-gray-600" fill="currentColor"
                                                    viewBox="0 0 448 512">
                                                    <path
                                                        d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Add to Cart Button -->
                                    {{-- <button
                                        class="flex items-center justify-center w-full py-3 mt-2 font-medium text-white transition duration-200 rounded-lg bg-navy-blue hover:bg-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Add to Cart
                                    </button> --}}
                                </div>
                            </div>

                            <!-- Tablet/Desktop View -->
                            <div class="items-center hidden gap-5 sm:flex">
                                <!-- Product Image & Details -->
                                <div class="flex items-center w-[20%]">
                                    <div class="flex-shrink-0 w-20 h-20">
                                        <img class="object-cover w-full h-full rounded"
                                            src="{{ asset('uploads/products/design-stack/' . $order->front_view) }}"
                                            alt="{{ $order->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="font-medium text-gray-800">{{ $order->name }}</h3>
                                    </div>
                                </div>


                                <!-- Material Selection -->
                                <div class="w-full">
                                    <x-input-label class="mb-1 font-bold text-gray-700" for="material"
                                        :value="__('Material')" />
                                    <select wire:model='material' x-on:change='$wire.addMaterial(event.target.value)'
                                        id="status"
                                        class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                        <option value="0">Select a material
                                        </option>
                                        @forelse ($materials as $material)
                                            <option value="{{ $material->id }}">
                                                {{ ucfirst($material->name) }} - ${{ $material->price }}</option>
                                        @empty
                                            <option value="">No materials available</option>
                                        @endforelse
                                    </select>
                                    <x-input-error :messages="$errors->get('material') ? 'Material Not Selected' : ''" class="mt-1" />
                                </div>

                                <!-- Size Selection -->
                                <div class="w-full">
                                    <x-input-label class="mb-1 font-bold text-gray-700" for="size"
                                        :value="__('Size')" />
                                    <select id="size" required wire:model="size"
                                        class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                        <option value="">
                                            Select Size
                                        </option>
                                        @foreach (['xs', 's', 'm', 'l', 'xl'] as $size)
                                            <option class="uppercase" value="{{ $size }}">
                                                {{ strtoupper($size) }}</option>
                                        @endforeach

                                    </select>

                                    <x-input-error :messages="$errors->get('size') ? 'Size Not Selected' : ''" class="mt-1" />
                                </div>


                                <!-- Quantity Controls -->
                                <div class="w-[10%] flex justify-center">
                                    <div class="flex items-center border rounded-md">
                                        <button wire:click="decrementQuantity" class="px-2 py-1">
                                            <svg class="w-3 h-3 text-gray-600" fill="currentColor"
                                                viewBox="0 0 448 512">
                                                <path
                                                    d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                            </svg>
                                        </button>
                                        <span class="px-3 py-1 text-sm text-black border-x"
                                            wire:text='quantity'></span>
                                        <button wire:click="incrementQuantity" class="px-2 py-1">
                                            <svg class="w-3 h-3 text-gray-600" fill="currentColor"
                                                viewBox="0 0 448 512">
                                                <path
                                                    d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Unit Price -->
                                <div class="w-[10%] text-center">
                                    <span class="text-sm font-medium text-gray-700">${{ $itemTotal }}</span>
                                </div>


                            </div>
                        </div>
                    </div>

                    <!-- Continue Shopping Button -->
                    <div class="p-4 border-t sm:p-6">
                        <span class="inline-flex items-center font-medium text-navy-blue hover:text-golden"
                            wire:click='closeOrderProduct'>
                            @svg('eva-arrow-back', 'w-4 h-4 mr-2')
                            Continue Shopping
                        </span>
                    </div>

                </div>

                <!-- Order Summary Section -->
                <div class="w-full lg:w-1/3 lg:mt-0">
                    <div class="overflow-hidden text-white rounded-r-lg shadow-md bg-navy-blue">
                        <div class="p-6 bg-white border-b border-white">
                            <h2 class="text-xl font-semibold text-navy-blue">Order Summary</h2>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Subtotal -->
                            <div class="flex justify-between">
                                <span class="text-sm font-medium uppercase">Subtotal</span>
                                <span>${{ $subTotal }}</span>
                            </div>

                            <!-- Shipping Option -->
                            <div class="w-full my-3 md:w-100">
                                <x-input-label class="text-sm font-extrabold text-white uppercase" for="location"
                                    :value="__('Location - Shipping Fee')" />
                                <select wire:model="location" id="location" wire:change="updateShippingFee"
                                    class="block w-full px-3 py-2 text-black border border-gray-500 rounded shadow-sm focus:outline-none focus:navy-blue sm:text-sm">
                                    <option value="0">Select Location</option>
                                    @forelse ($shipping_rates as $shipping_rate)
                                        <option value="{{ $shipping_rate->id }}">
                                            {{ $shipping_rate->location }} - {{ $shipping_rate->rate }} </option>
                                    @empty
                                        <option value=""> . . . </option>
                                    @endforelse
                                </select>
                                <x-input-error :messages="$errors->get('location') ? 'Location Not Selected' : ''" class="mt-1" />
                            </div>


                            <!-- Phone Number -->
                            <div class="col-span-12 md:col-span-3">
                                <label for="phone_no" class="block text-sm font-medium text-white uppercase">
                                    Phone No
                                </label>
                                <div class="mt-1">
                                    <input type="text" wire:model="phone_no" id="phone_no" autocomplete="number"
                                        required
                                        class="block w-full text-gray-700 border-gray-300 rounded-md shadow-sm focus:ring-navy-blue focus:border-navy-blue sm:text-sm">
                                </div>
                                <x-input-error :messages="$errors->get('phone_no') ? 'Mobile Number Is Required For Delivery' : ''" class="mt-1" />
                            </div>

                            <!-- Shipping Address -->
                            <div>
                                <label for="address" class="block mb-2 text-sm font-medium uppercase">Shipping
                                    Address</label>
                                <textarea wire:model="address" id="address" class="w-full p-3 text-sm text-gray-700 bg-white rounded"
                                    rows="3" placeholder="Enter your shipping address"></textarea>
                                <x-input-error :messages="$errors->get('address') ? 'Address Not Selected' : ''" class="mt-1" />
                            </div>

                            <!-- Total Cost -->
                            <div class="pt-4 border-t border-blue-800">
                                <div class="flex justify-between font-medium">
                                    <span class="text-sm uppercase">Discount ({{ $discount }}%)</span>
                                    @if ($discount > 0)
                                        @if ($useCurrentDiscount === 'used')
                                            <x-mary-button label="Discounted"
                                                class="bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                                wire:click="useDiscount('false')" spinner />
                                        @else
                                            <x-mary-button label="Use Discount"
                                                class="bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                                wire:click="useDiscount('true')" spinner />
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Total Cost -->
                            <div class="pt-4 border-t border-blue-800">
                                <div class="flex justify-between font-medium">
                                    <span class="text-sm uppercase">Total cost</span>
                                    <span>${{ $totalPrice }}</span>
                                </div>
                            </div>

                            <!-- Checkout Button -->
                            <x-mary-button spinner wire:click="completeCheckout"
                                class="w-full px-4 py-3 font-medium uppercase transition duration-200 bg-white rounded text-navy-blue hover:bg-golden hover:text-white">
                                Checkout
                            </x-mary-button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
