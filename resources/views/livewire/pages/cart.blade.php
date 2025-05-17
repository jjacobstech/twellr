<?php

use App\Models\Cart;
use App\Models\User;
use App\Models\State;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\Product;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\ShippingFee;
use App\Models\Transaction;
use App\Models\AdminSetting;
use App\Models\Notification;
use Livewire\Volt\Component;
use App\Models\PlatformEarning;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, layout, mount, computed, uses};

layout('layouts.app');
uses(Toast::class);

state([
    'cartItems' => null,
    'totalPrice' => 0,
    'subTotal' => 0,
    'address' => '',
    'shipping_fee' => 0,
    'shipping_rates' => null,
    'materials' => null,
    'materialPrice' => null,
    'countries' => [],
    'states' => [],
    'phone_no' => '',
    'size' => '',
    'material' => '',
    'country' => '',
    'state' => '',
    'location' => '',
]);

mount(function () {
    $user = auth()->user();
    $this->address = $user->address;
    $this->phone_no = $user->phone_no;
    $this->country = $user->country_id;
    $this->state = $user->state_id;

    $this->materials = Material::all();
    $this->countries = Country::all();
    $this->states = $user->state_id ? State::where('country_id', $user->country_id)->get() : [];

    $this->shipping_rates = ShippingFee::all();

    $this->loadCartItems();
});

$loadCartItems = function () {
    $this->cartItems = Cart::where('user_id', Auth::id())->with('product', 'material')->get();
    $this->calculateTotalPrice();
};

$updateShippingFee = function () {
    $shipping = ShippingFee::where('id', '=', $this->location)->first();
    $this->shipping_fee = $shipping->rate;
    $this->loadCartItems();
};

$calculateTotalPrice = function () {
    $this->subTotal = $this->cartItems->sum(function ($item) {
        $basePrice = $item->product->price;
        $materialPrice = $item->material ? $item->material->price : 0;
        return ($basePrice + $materialPrice) * $item->quantity;
    });
    $this->totalPrice = $this->subTotal + $this->shipping_fee;
};

$updateMaterial = function ($id, $value) {
    $item = Cart::where('user_id', Auth::id())->where('id', '=', $id)->first();
    $item->material_id = $value;
    $item->save();
    $this->loadCartItems();
};

$updateSize = function ($id, $value) {
    $item = Cart::where('user_id', Auth::id())->where('id', '=', $id)->first();
    $item->size = $value;
    $item->save();
    $this->loadCartItems();
};

$incrementQuantity = function (Cart $cartItem) {
    $cartItem->increment('quantity');
    $this->loadCartItems();
};

$getStates = function ($countryId = null) {
    if (!$countryId) {
        return [];
    }

    $this->states = State::where('country_id', $countryId)->get();
    return $this->states;
};
$decrementQuantity = function (Cart $cartItem) {
    if ($cartItem->quantity > 1) {
        $cartItem->decrement('quantity');
        $this->loadCartItems();
    }
};

$removeFromCart = function (Cart $cartItem) {
    $cartItem->delete();
    $this->loadCartItems();
    $this->success('Item removed from cart!');
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

$checkout = function () {
    if (empty(Auth::user()->phone_no)) {
        session()->put('was_redirected', true);
        $this->redirectIntended(route('settings'), true);
    } elseif ($this->cartItems->isEmpty()) {
        $this->toast('Your cart is empty!', 'warning');
    } else {
        $orderData = (object) $this->validate([
            'address' => ['required', 'min:10'],
            'phone_no' => ['required'],
            'location' => ['required'],
        ]);

        if ($this->totalPrice > Auth::user()->wallet_balance) {
            return $this->error("Low Balance $" . Auth::user()->wallet_balance, 'Your wallet balance is low. Add funds to continue');
        } else {
            foreach ($this->cartItems as $cartItem) {
                $product = $cartItem->product;
                $material = $cartItem->material;
                $size = $cartItem->size;

                $ref_no = $this->generateReferenceNumber();

                $buyer = User::where('id', '=', Auth::id())->first();
                $itemPrice = ($product->price + $material->price) * $cartItem->quantity;
                $buyer->wallet_balance = $buyer->wallet_balance - $itemPrice;
                $deducted = $buyer->save();

                if (!$deducted) {
                    return $this->error('Transaction Error - Buyer Deduction Service', 'An error has occured, but we are working on it');
                }

                $creative = User::where('id', '=', $product->user_id)->first();
                $creative->wallet_balance = $creative->wallet_balance + $product->price;
                $deposited = $creative->save();

                if (!$deposited) {
                    $buyer->wallet_balance = $buyer->wallet_balance + $this->totalPrice;
                    $deducted = $buyer->save();
                    return $this->error('Transaction Error - Creative Deposit Service', 'An error has occured, but we are working on it');
                }

                $transaction = Transaction::create([
                    'user_id' => $product->user_id,
                    'buyer_id' => Auth::id(),
                    'amount' => ($product->price + $material->price) * $cartItem->quantity,
                    'ref_no' => $ref_no,
                    'transaction_type' => 'sales',
                    'status' => 'pending',
                ]);

                if (!$transaction) {
                    return $this->error('Transaction Error - Transaction Registration Service', 'An error has occured, but we are working on it');
                }

                $purchase = Purchase::create([
                    'transactions_id' => $transaction->id,
                    'user_id' => $product->user_id,
                    'buyer_id' => Auth::id(),
                    'address' => $orderData->address,
                    'product_id' => $product->id,
                    'delivery_status' => 'pending',
                    'amount' => ($product->price + $material->price) * $cartItem->quantity,
                    'phone_no' => $orderData->phone_no,
                    'location_id' => $this->location,
                    'quantity' => $cartItem->quantity,
                    'product_name' => $product->name,
                    'product_category' => $product->category,
                    'material_id' => $material->id,
                    'size' => $size,
                ]);

                if (!$purchase) {
                    Transaction::where('id', '=', $transaction->id)->delete();
                    return $this->error('Transaction Error - Purchase Registration Service', 'An error has occured, but we are working on it');
                }

                $platformEarning = PlatformEarning::create([
                    'purchase_id' => $purchase->id,
                    'transaction_id' => $transaction->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $material->price,
                    'total' => $material->price * $cartItem->quantity,
                    'fee_type' => 'material sales',
                ]);

                if (!$platformEarning) {
                    Transaction::where('id', '=', $transaction->id)->delete();
                    Purchase::where('id', '=', $purchase->id)->delete();
                    return $this->error('Transaction Error - Notification Service', 'An error has occured, but we are working on it');
                }

                $seller = $product->user_id;
                $buyerName = Auth::user()->firstname . ' ' . Auth::user()->lastname;

                $notification = Notification::create([
                    'user_id' => $seller,
                    'title' => $product->name . ' design has been ordered',
                    'message' => "$buyerName has purchased " . $cartItem->quantity . ' units of ' . $product->name,
                    'type' => 'sales',
                ]);

                if (!$notification) {
                    Transaction::where('id', '=', $transaction->id)->delete();
                    Purchase::where('id', '=', $purchase->id)->delete();
                    PlatformEarning::where('id', '=', $platformEarning->id)->delete();
                    return $this->error('Transaction Error - Notification Service', 'An error has occured, but we are working on it');
                }

                $cartItem->delete();
            }

            $buyer = User::where('id', '=', Auth::id())->first();
            $itemPrice = $this->shipping_fee;
            $buyer->wallet_balance = $buyer->wallet_balance - $itemPrice;
            $deducted = $buyer->save();

            // Cart::where('user_id', Auth::id())->delete();
            $ref_no = $this->generateReferenceNumber();

            $transaction = Transaction::create([
                'buyer_id' => Auth::id(),
                'amount' => $this->shipping_fee,
                'ref_no' => $ref_no,
                'transaction_type' => 'shipping_fee',
                'status' => 'successful',
            ]);

            if (!$transaction) {
                return $this->error('Transaction Error - Transaction Registration Service', 'An error has occured, but we are working on it');
            }

            $platformEarning = PlatformEarning::create([
                'quantity' => 1,
                'transaction_id' => $transaction->id,
                'price' => $this->shipping_fee,
                'total' => $this->shipping_fee,
                'fee_type' => 'shipping fee',
            ]);

            if (!$platformEarning) {
                Transaction::where('id', '=', $transaction->id)->delete();
                return $this->error('Transaction Error - Platform Service', 'An error has occured, but we are working on it');
            }
        }

        $this->success('Checkout Successful', 'Your order is on its way');
        $this->loadCartItems();
    }
};
?>

<div class="w-full h-screen overflow-y-scroll bg-gray-100 pb-44 lg:pb-20 scrollbar-none">

    <div wire:loading
        class="absolute py-3 mb-6 text-white transition-opacity duration-500 border rounded alert-info alert top-5 right-1 bg-navy-blue border-navy-blue"
        role="alert">
        <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg> Loading . . .
    </div>

    @error('address')
        {{ $this->warning('Address Not Selected') }}
    @enderror

    @error('phone_no')
        {{ $this->warning('Mobile Number Is Required For Delivery', 'Please add your mobile number') }}
    @enderror

    @error('location')
        {{ $this->warning('Location Not Selected') }}
    @enderror


    {{-- <form wire:submit.prevent='checkout'> --}}
    <!-- Main Container -->
    <div class="px-4 py-4 mx-auto max-w-20xl sm:px-6 lg:px-4 ">
        <h1 class="mb-6 text-3xl font-bold text-gray-700">Shopping Cart</h1>

        <!-- Cart Container -->
        <div class="flex flex-col gap-4 lg:flex-row">
            <!-- Cart Items Section -->
            <div class="w-full overflow-hidden bg-white rounded-lg shadow-md lg:w-2/3">
                <!-- Cart Header -->
                <div class="flex flex-col items-start justify-between p-4 border-b sm:p-6 sm:flex-row sm:items-center">
                    <h2 class="text-lg font-semibold text-gray-700">Your Items</h2>
                    <p class="mt-1 text-sm text-gray-500 sm:mt-0">{{ count($cartItems) }} Items</p>
                </div>

                <!-- Empty Cart Message -->
                @if (count($cartItems) == 0)
                    <div class="p-8 text-center">
                        <p class="mb-4 text-gray-500">Your cart is currently empty.</p>
                        <a href="{{ route('market.place') }}"
                            class="inline-flex items-center font-medium text-navy-blue hover:text-golden">
                            @svg('eva-arrow-back', 'w-4 h-4 mr-2')
                            Browse Products
                        </a>
                    </div>
                @else
                    <!-- Cart Items -->
                    <div class="divide-y">
                        @foreach ($cartItems as $item)
                            <div class="p-4 sm:p-6">

                                <!-- Mobile View (Optimized) -->
                                <div class="flex flex-col p-4 bg-white rounded-lg shadow sm:hidden">
                                    <!-- Product Header with Image and Basic Info -->
                                    <div class="flex mb-5">

                                        <!-- Product Image -->
                                        <div class="flex-shrink-0 w-24 h-24">
                                            <img class="object-cover w-full h-full rounded-md shadow-sm"
                                                src="{{ asset('uploads/products/design-stack/' . $item->product->front_view) }}"
                                                alt="{{ $item->product->name }}">

                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-grow ml-4">
                                            <h3 class="text-lg font-semibold text-gray-800">
                                                {{ $item->product->name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-red-500">
                                                {{ strtoupper($item->product->size) }}
                                            </p>
                                            <p class="mt-1 text-base font-medium text-gray-700">
                                                ${{ $item->product->price }}</p>
                                        </div>
                                    </div>

                                    <!-- Product Options Section -->
                                    <div class="space-y-4">
                                        <!-- Material Selection -->
                                        <div class="w-full">
                                            <x-input-label class="mb-1 font-bold text-gray-700" for="material"
                                                :value="__('Material')" />
                                            <select id="status" required
                                                x-on:change='$wire.updateMaterial({{ $item->id }},event.target.value)'
                                                class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                                <option value="">
                                                    Select a material
                                                </option>
                                                @forelse ($materials as $material)
                                                    <option
                                                        @if ($item->material == null) @else
                                                     @if ($item->material->id == $material->id) selected @endif
                                                        @endif

                                                        value="{{ $material->id }}">
                                                        {{ ucfirst($material->name) }} - ${{ $material->price }}
                                                    </option>
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
                                                <select id="size" required wire:model="currentSize"
                                                    x-on:change='$wire.updateSize({{ $item->id }}, event.target.value)'
                                                    class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                                    <option value="">
                                                        Select Size
                                                    </option>
                                                    <option @if ($item->size == 'xs') selected @endif
                                                        value="xs">
                                                        XS
                                                    </option>

                                                    <option @if ($item->size == 's') selected @endif
                                                        value="s">
                                                        S
                                                    </option>

                                                    <option @if ($item->size == 'm') selected @endif
                                                        value="m">
                                                        M
                                                    </option>

                                                    <option @if ($item->size == 'l') selected @endif
                                                        value="l">
                                                        L
                                                    </option>

                                                    <option @if ($item->size == 'xl') selected @endif
                                                        value="xl">
                                                        XL
                                                    </option>

                                                </select>
                                                <x-input-error :messages="$errors->get('currentSize') ? 'Size Not Selected' : ''" class="mt-1" />
                                            </div>

                                            <!-- Quantity Controls -->
                                            <div class="w-full mt-2">
                                                <x-input-label class="mb-1 font-bold text-gray-700" for="quantity"
                                                    :value="__('Quantity')" />
                                                <div
                                                    class="flex items-center justify-between overflow-hidden border border-gray-300 rounded-lg">
                                                    <button wire:click="decrementQuantity({{ $item }})"
                                                        class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 transition">
                                                        <svg class="w-4 h-4 text-gray-600" fill="currentColor"
                                                            viewBox="0 0 448 512">
                                                            <path
                                                                d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                        </svg>
                                                    </button>
                                                    <span
                                                        class="flex-grow px-4 py-2 font-medium text-center text-gray-800">{{ $item->quantity }}</span>
                                                    <button wire:click="incrementQuantity({{ $item }})"
                                                        class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 transition">
                                                        <svg class="w-4 h-4 text-gray-600" fill="currentColor"
                                                            viewBox="0 0 448 512">
                                                            <path
                                                                d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Item Total -->
                                            <div class="text-right">
                                                <p class="font-medium text-gray-800">
                                                    @if ($item->material == null)
                                                        ${{ $item->product->price * $item->quantity }}
                                                    @else
                                                        ${{ ($item->product->price + $item->material->price) * $item->quantity }}
                                                    @endif
                                                </p>
                                                <button wire:click="removeFromCart({{ $item->id }})"
                                                    class="mt-1 text-xs text-red-600">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Tablet/Desktop View -->
                                    <div class="items-center hidden gap-5 sm:flex">

                                        <!-- Product Image & Details -->
                                        <div class="grid items-center w-20 mr-2 text-center">
                                            <div class="flex-shrink-0 w-20 h-20">
                                                <img class="object-cover w-full h-full rounded"
                                                    src="{{ asset('uploads/products/design-stack/' . $item->product->front_view) }}"
                                                    alt="{{ $item->product->name }}">
                                            </div>
                                            <div class="">
                                                <h3 class="font-medium text-gray-800">{{ $item->product->name }}</h3>
                                            </div>
                                        </div>

                                        <!-- Material -->
                                        <div class=" w-[30%] mx-2">
                                            <x-input-label class="font-extrabold bg-white " for="material"
                                                :value="__('Material')" />
                                            <select id="status" required
                                                x-on:change='$wire.updateMaterial({{ $item->id }}, event.target.value)'
                                                class="w-full px-3 py-2 text-black border-gray-500 rounded-md shadow-sm blockborder focus:outline-none focus:navy-blue sm:text-sm">
                                                <option value="">
                                                    Select a material</option>
                                                @forelse ($materials as $material)
                                                    <option
                                                        @if ($item->material == null) @else
                                                     @if ($item->material->id == $material->id) selected @endif
                                                        @endif

                                                        value="{{ $material->id }}">
                                                        {{ ucfirst($material->name) }} - ${{ $material->price }} -
                                                        {{ $material->availability }}</option>
                                                    @empty
                                                        <option value=""> . . . </option>
                                                    @endforelse
                                                </select>
                                                <x-input-error :messages="$errors->get('currentMaterial')
                                                    ? 'Material Not Selected'
                                                    : ''" class="mt-1" />

                                            </div>

                                            <!-- sizes -->
                                            <div class="grid items-baseline w-[20%]   text-gray-700">
                                                <x-input-label class="font-extrabold bg-white " for="size"
                                                    :value="__('Size')" />
                                                <select id="size" required
                                                    x-on:change='$wire.updateSize({{ $item->id }} , event.target.value)'
                                                    class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                                    <option value="">
                                                        Select Size
                                                    </option>
                                                    <option @if ($item->size == 'xs') selected @endif value="xs">
                                                        XS
                                                    </option>

                                                    <option @if ($item->size == 's') selected @endif value="s">
                                                        S
                                                    </option>

                                                    <option @if ($item->size == 'm') selected @endif value="m">
                                                        M
                                                    </option>

                                                    <option @if ($item->size == 'l') selected @endif value="l">
                                                        L
                                                    </option>

                                                    <option @if ($item->size == 'xl') selected @endif value="xl">
                                                        XL
                                                    </option>

                                                </select>
                                                <x-input-error :messages="$errors->get('size') ? 'Size Not Selected' : ''" class="mt-1" />


                                            </div>

                                            <!-- Quantity Controls -->
                                            <div class="w-[10%] flex justify-center">
                                                <div class="flex items-center border rounded-md">
                                                    <button wire:click="decrementQuantity({{ $item }})"
                                                        class="px-2 py-1">
                                                        <svg class="w-3 h-3 text-gray-600" fill="currentColor"
                                                            viewBox="0 0 448 512">
                                                            <path
                                                                d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                        </svg>
                                                    </button>
                                                    <span
                                                        class="px-3 py-1 text-sm text-black border-x">{{ $item->quantity }}</span>
                                                    <button wire:click="incrementQuantity({{ $item }})"
                                                        class="px-2 py-1">
                                                        <svg class="w-3 h-3 text-gray-600" fill="currentColor"
                                                            viewBox="0 0 448 512">
                                                            <path
                                                                d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Unit Price -->
                                            <div class="w-[7%] text-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    @if ($item->material == null)
                                                        ${{ $item->product->price }}
                                                    @else
                                                        ${{ $item->product->price + $item->material->price }}
                                                    @endif
                                                </span>
                                            </div>

                                            <!-- Item Total -->
                                            <div class="w-[8%] text-right grid ">
                                                <span class="text-sm font-medium text-gray-800">
                                                    @if ($item->material == null)
                                                        ${{ $item->product->price * $item->quantity }}
                                                    @else
                                                        ${{ ($item->product->price + $item->material->price) * $item->quantity }}
                                                    @endif
                                                </span>
                                                  <span wire:click="removeFromCart({{ $item }})" class="absolute mt-8 right-10  hover:text-red-400 lg:hidden cursor-pointer text-red-500 font-bold">Remove</span>
                                                    <span wire:click="removeFromCart({{ $item }})" class="absolute mt-8 hidden lg:block ml-5 hover:text-red-400 cursor-pointer text-red-500 font-bold">Remove</span>
                                            </div>

                                        </div>


                                    </div>
                                @endforeach
                            </div>

                            <!-- Continue Shopping Button -->
                            <div class="p-4 border-t sm:p-6">
                                <a href="{{ route('market.place') }}"
                                    class="inline-flex items-center font-medium text-navy-blue hover:text-golden">
                                    @svg('eva-arrow-back', 'w-4 h-4 mr-2')
                                    Continue Shopping
                                </a>
                            </div>
                        @endif
                    </div>
                    <!-- Order Summary Section -->
                    <div class="w-full mt-6 lg:w-1/3 lg:mt-0">
                        <div class="overflow-hidden text-white rounded-lg shadow-md bg-navy-blue">
                            <div class="p-6 border-b border-blue-800">
                                <h2 class="text-xl font-semibold">Order Summary</h2>
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
                                        <option value="">Select Location</option>
                                        @forelse ($shipping_rates as $shipping_rate)
                                            <option value="{{ $shipping_rate->id }}">
                                                {{ $shipping_rate->location }} - {{ $shipping_rate->rate }} </option>
                                        @empty
                                            <option value=""> . . . </option>
                                        @endforelse
                                    </select>
                                    <x-input-error :messages="$errors->get('location') ? 'Location Not Selected' : ''" class="mt-1" />
                                </div>


                                <!-- Country -->
                                {{-- @if (!isset(Auth::user()->country_id))
                                    <div class="w-full my-3 md:w-100">
                                        <x-input-label class="text-sm font-extrabold text-white uppercase" for="country"
                                            :value="__('Country')" />
                                        <select wire:model.live="country" id="country"
                                            class="block w-full px-3 py-2 text-black border border-gray-500 rounded shadow-sm focus:outline-none focus:navy-blue sm:text-sm">
                                            <option value="" @click="$wire.getStates()">Select a Country</option>
                                            @forelse ($countries as $country)
                                                <option @if (Auth::user()->country_id === $country->id) selected @endif
                                                    @click="$wire.getStates({{ $country->id }})"
                                                    value="{{ $country->id }}">
                                                    {{ $country->name }}</option>
                                            @empty
                                                <option value=""> . . . </option>
                                            @endforelse
                                        </select>
                                        <x-input-error :messages="$errors->get('country') ? 'Country Not Selected' : ''" class="mt-1" />
                                    </div>
                                    @endif --}}


                                <!-- State -->
                                {{-- @if (!isset(Auth::user()->state_id))
                                    <div class="w-full my-3 md:w-100">
                                        <x-input-label class="text-sm font-extrabold text-white uppercase" for="state"
                                            :value="__('State')" />
                                        <select wire:model.live="state" id="state"
                                            class="block w-full px-3 py-2 text-black border border-gray-500 rounded shadow-sm focus:outline-none focus:navy-blue sm:text-sm">
                                            <option value="">Select a State</option>
                                            @forelse ($states as $state)
                                                <option @if (Auth::user()->state_id === $state->id) selected @endif
                                                    value="{{ $state->id }}">{{ ucfirst($state->name) }}</option>

                                            @empty
                                                <option value=""> . . . </option>
                                            @endforelse
                                        </select>
                                        <x-input-error :messages="$errors->get('state') ? 'State Not Selected' : ''" class="mt-1" />
                                    </div>
                                    @endif --}}

                                <!-- Phone Number -->
                                <div class="col-span-12 md:col-span-3">
                                    <label for="phone_no" class="block text-sm font-medium text-white uppercase">
                                        Phone No
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.live="phone_no" id="phone_no"
                                            autocomplete="number" required
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
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>

                                <!-- Total Cost -->
                                <div class="pt-4 border-t border-blue-800">
                                    <div class="flex justify-between font-medium">
                                        <span class="text-sm uppercase">Total cost</span>
                                        <span>${{ $totalPrice }}</span>
                                    </div>
                                </div>

                                <!-- Checkout Button -->
                                <x-mary-button spinner type="submit" wire:click='checkout'
                                    class="w-full px-4 py-3 font-medium uppercase transition duration-200 bg-white rounded text-navy-blue hover:bg-golden hover:text-white">
                                    Checkout
                                    </x-marybutton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
