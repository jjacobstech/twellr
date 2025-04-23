<?php

use App\Models\Cart;
use App\Models\User;
use App\Models\State;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\Product;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\AdminSetting;
use App\Models\Notification;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    use Toast;

    public $cartItems;
    public $totalPrice = 0;
    public $address;
    public $shipping_fee;
    public $materials;
    public $materialPrice;
    public $countries = [];
    public $states = [];
    public $phone_no;
    public $size;
    public $material;
    public $country;
    public $state;

    public function mount()
    {
        $user = auth()->user();
        $this->address = $user->address;
        $this->phone_no = $user->phone_no;
        $this->country = $user->country_id;
        $this->state = $user->state_id;

        $this->materials = Material::all();
        $this->countries = Country::all();
        $this->states = $user->state_id ? State::where('country_id', $user->country_id)->get() : [];

        $settings = AdminSetting::first();
        $this->shipping_fee = $settings ? $settings->shipping_fee : 0;

        $this->loadCartItems();
    }

    public function loadCartItems()
    {
        $this->cartItems = Cart::where('user_id', Auth::id())->with('product', 'material')->get();
        $this->calculateTotalPrice();
    }

    public function calculateTotalPrice()
    {
        $this->totalPrice = $this->cartItems->sum(function ($item) {
            $basePrice = $item->product->price;
            $materialPrice = $item->material ? $item->material->price : 0;
            return ($basePrice + $materialPrice) * $item->quantity;
        });
    }

    public function addMaterial($material, $id)
    {
        $item = Cart::where('user_id', Auth::id())->where('id', '=', $id)->first();
        $item->material_id = $material;
        $item->save();
        $this->loadCartItems();
    }

    public function addSize($size, $id)
    {
        $item = Cart::where('user_id', Auth::id())->where('id', '=', $id)->first();
        $item->size = $size;
        $item->save();
        $this->loadCartItems();
    }

    public function incrementQuantity(Cart $cartItem)
    {
        $cartItem->increment('quantity');
        $this->loadCartItems();
    }

    public function getStates($countryId = null)
    {
        if (!$countryId) {
            return [];
        }

        $this->states = State::where('country_id', $countryId)->get();
        return $this->states;
    }
    public function decrementQuantity(Cart $cartItem)
    {
        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
            $this->loadCartItems();
        }
    }

    public function removeFromCart(Cart $cartItem)
    {
        $cartItem->delete();
        $this->loadCartItems();
        $this->success('Item removed from cart!');
    }

    public function generateReferenceNumber()
    {
        $prefix = 'TRX';
        do {
            $timestamp = now()->format('YmdHis');
            $randomString = strtoupper(Str::random(6));
            $reference_no = $prefix . $timestamp . $randomString . Auth::id();

            $exists = Transaction::where('ref_no', '=', $reference_no)->first();
        } while ($exists);

        return $reference_no;
    }

    public function checkout()
    {
        if (empty(Auth::user()->phone_no)) {
            session()->put('was_redirected', true);
            $this->redirectIntended(route('settings'), true);
        } elseif ($this->cartItems->isEmpty()) {
            $this->toast('Your cart is empty!', 'warning');
        } else {
            $orderData = (object) $this->validate(
                [
                    'country' => ['required'],
                    'state' => ['required'],
                    'address' => ['required', 'min:10'],
                    'phone_no' => ['required'],
                ],
                [
                    'address.required' => $this->warning('Address Not Selected'),
                    'country.required' => $this->warning('Country Not Selected'),
                    'state.required' => $this->warning('State Not Selected'),
                    'phone_no.required' => $this->warning('Mobile Number Is Required For Delivery'),
                ],
            );

            if ($this->totalPrice > Auth::user()->wallet_balance) {
                return $this->error("Low Balance $" . Auth::user()->wallet_balance, 'Your wallet balance is low. Add funds to continue');
            } else {
                foreach ($this->cartItems as $cartItem) {
                    $product = $cartItem->product;
                    $material = $cartItem->material;
                    $size = $cartItem->size;

                    $ref_no = $this->generateReferenceNumber();

                    $transaction = Transaction::create([
                        'user_id' => $product->user_id,
                        'buyer_id' => Auth::id(),
                        'amount' => $product->price * $cartItem->quantity,
                        'ref_no' => $ref_no,
                        'transaction_type' => 'sales',
                        'status' => 'pending',
                    ]);

                    if (!$transaction) {
                        return $this->error('Withdrawal Error - Transaction Registration Service', 'An error has occured, but we are working on it');
                    }

                    $purchase = Purchase::create([
                        'transactions_id' => $transaction->id,
                        'user_id' => $product->user_id,
                        'buyer_id' => Auth::id(),
                        'address' => $orderData->address,
                        'product_id' => $product->id,
                        'delivery_status' => 'pending',
                        'amount' => $this->totalPrice,
                        'phone_no' => $orderData->phone_no,
                        'location' => "$orderData->state, $orderData->country",
                        'quantity' => $cartItem->quantity,
                        'product_name' => $product->name,
                        'product_category' => $product->category,
                        'material' => $material->id,
                        'size' => $size,
                    ]);

                    if (!$purchase) {
                        Transaction::where('id', '=', $transaction->id)->delete();
                        return $this->error('Withdrawal Error - Purchase Registration Service', 'An error has occured, but we are working on it');
                    }

                    $buyer = User::where('id', '=', Auth::id())->first();
                    $buyer->wallet_balance = $buyer->wallet_balance - $this->totalPrice;
                    $deducted = $buyer->save();

                    if (!$deducted) {
                        Transaction::where('id', '=', $transaction->id)->delete();
                        Purchase::where('id', '=', $purchase->id)->delete();
                        return $this->error('Withdrawal Error - Buyer Deduction Service', 'An error has occured, but we are working on it');
                    }

                    $creative = User::where('id', '=', $product->user_id)->first();
                    $creative->wallet_balance = $creative->wallet_balance + $product->price;
                    $deposited = $creative->save();

                    if (!$deposited) {
                        Transaction::where('id', '=', $transaction->id)->delete();
                        Purchase::where('id', '=', $purchase->id)->delete();
                        $buyer->wallet_balance = $buyer->wallet_balance + $this->totalPrice;
                        $deducted = $buyer->save();
                        return $this->error('Withdrawal Error - Creative Deposit Service', 'An error has occured, but we are working on it');
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
                        $buyer->wallet_balance = $buyer->wallet_balance + $this->totalPrice;
                       $buyer->save();
                        $creative->wallet_balance = $creative->wallet_balance - $this->order->price;
                       $creative->save();
                        return $this->error('Withdrawal Error - Notification Service', 'An error has occured, but we are working on it');
                    }
                }

                Cart::where('user_id', Auth::id())->delete();

                $this->success('Checkout Successful', 'Your order is on its way');
                $this->loadCartItems();
            }
        }
    }
}; ?>

<div class="w-full h-screen pb-20 overflow-y-scroll bg-gray-100">
    <!-- Success Toast -->


    <form wire:submit.prevent='checkout'>
        <!-- Main Container -->
        <div class="px-4 py-4 mx-auto max-w-20xl sm:px-6 lg:px-4">
            <h1 class="mb-6 text-3xl font-bold text-gray-700">Shopping Cart</h1>

            <!-- Cart Container -->
            <div class="flex flex-col gap-4 lg:flex-row">
                <!-- Cart Items Section -->
                <div class="w-full overflow-hidden bg-white rounded-lg shadow-md lg:w-2/3">
                    <!-- Cart Header -->
                    <div
                        class="flex flex-col items-start justify-between p-4 border-b sm:p-6 sm:flex-row sm:items-center">
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
                                                    class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                                    <option wire:click='addMaterial("",{{ $item->id }})'
                                                        value="">
                                                        Select a material
                                                    </option>
                                                    @forelse ($materials as $material)
                                                        <option
                                                            @if ($item->material == null) @else
                                                     @if ($item->material->id == $material->id) selected @endif
                                                            @endif
                                                            wire:click='addMaterial({{ $material->id }},{{ $item->id }})'
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
                                                    <select id="size" required
                                                        class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                                        <option wire:click='addSize("",{{ $item->id }})'
                                                            value="">
                                                            Select Size
                                                        </option>
                                                        <option @if ($item->size == 'xs') selected @endif
                                                            wire:click="addSize('xs',{{ $item->id }})" value="xs">
                                                            XS
                                                        </option>

                                                        <option @if ($item->size == 's') selected @endif
                                                            wire:click="addSize('s',{{ $item->id }})" value="s">
                                                            S
                                                        </option>

                                                        <option @if ($item->size == 'm') selected @endif
                                                            wire:click="addSize('m',{{ $item->id }})" value="m">
                                                            M
                                                        </option>

                                                        <option @if ($item->size == 'l') selected @endif
                                                            wire:click="addSize('l',{{ $item->id }})" value="l">
                                                            L
                                                        </option>

                                                        <option @if ($item->size == 'xl') selected @endif
                                                            wire:click="addSize('xl',{{ $item->id }})" value="xl">
                                                            XL
                                                        </option>

                                                    </select>
                                                    <x-input-error :messages="$errors->get('size') ? 'Size Not Selected' : ''" class="mt-1" />
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
                                                    class="w-full px-3 py-2 text-black border-gray-500 rounded-md shadow-sm blockborder focus:outline-none focus:navy-blue sm:text-sm">
                                                    <option wire:click='addMaterial("",{{ $item->id }})'
                                                        value="">
                                                        Select a material</option>
                                                    @forelse ($materials as $material)
                                                        <option
                                                            @if ($item->material == null) @else
                                                     @if ($item->material->id == $material->id) selected @endif
                                                            @endif
                                                            wire:click='addMaterial({{ $material->id }},{{ $item->id }})'
                                                            value="{{ $material->id }}">
                                                            {{ ucfirst($material->name) }} - ${{ $material->price }} -
                                                            {{ $material->availability }}</option>
                                                        @empty
                                                            <option value=""> . . . </option>
                                                        @endforelse
                                                    </select>
                                                    <x-input-error :messages="$errors->get('material') ? 'Material Not Selected' : ''" class="mt-1" />

                                                </div>

                                                <!-- sizes -->
                                                <div class="grid items-baseline w-[20%]   text-gray-700">
                                                    <x-input-label class="font-extrabold bg-white " for="size"
                                                        :value="__('Size')" />
                                                    <select id="size" required
                                                        class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                                        <option wire:click='addSize("",{{ $item->id }})' value="">
                                                            Select Size
                                                        </option>
                                                        <option @if ($item->size == 'xs') selected @endif
                                                            wire:click="addSize('xs',{{ $item->id }})" value="xs">
                                                            XS
                                                        </option>

                                                        <option @if ($item->size == 's') selected @endif
                                                            wire:click="addSize('s',{{ $item->id }})" value="s">
                                                            S
                                                        </option>

                                                        <option @if ($item->size == 'm') selected @endif
                                                            wire:click="addSize('m',{{ $item->id }})" value="m">
                                                            M
                                                        </option>

                                                        <option @if ($item->size == 'l') selected @endif
                                                            wire:click="addSize('l',{{ $item->id }})" value="l">
                                                            L
                                                        </option>

                                                        <option @if ($item->size == 'xl') selected @endif
                                                            wire:click="addSize('xl',{{ $item->id }})" value="xl">
                                                            XL
                                                        </option>

                                                    </select>
                                                    <x-input-error :messages="$errors->get('size') ? 'Size Not Selected' : ''" class="mt-1" />

                                                    {{-- <a href="#" class="hidden ml-auto text-sm text-gray-500 underline md:block">Size
                                        Guide
                                             </a> --}}
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
                                                <div class="w-[8%] text-right">
                                                    <span class="text-sm font-medium text-gray-800">
                                                        @if ($item->material == null)
                                                            ${{ $item->product->price * $item->quantity }}
                                                        @else
                                                            ${{ ($item->product->price + $item->material->price) * $item->quantity }}
                                                        @endif
                                                    </span>
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
                                        <span>${{ $totalPrice }}</span>
                                    </div>

                                    <!-- Shipping Option -->
                                    <div>
                                        <label class="block mb-2 text-sm font-medium uppercase">Shipping</label>
                                        <select class="w-full p-2 text-sm text-gray-700 bg-white rounded">
                                            <option>Standard Shipping - {{ $shipping_fee ? "$$shipping_fee" : 'Free' }}
                                            </option>
                                        </select>
                                    </div>


                                    <!-- Country -->
                                    {{-- @if (!isset(Auth::user()->country_id)) --}}
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
                                    {{-- @endif --}}


                                    <!-- State -->
                                    {{-- @if (!isset(Auth::user()->state_id)) --}}
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
                                    {{-- @endif --}}

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
                                        <x-input-error :messages="$errors->get('phone_no')
                                            ? 'Mobile Number Is Required For Delivery'
                                            : ''" class="mt-1" />
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
                                            <span>${{ $totalPrice + $shipping_fee }}</span>
                                        </div>
                                    </div>

                                    <!-- Checkout Button -->
                                    <x-mary-button spinner type="submit"
                                        class="w-full px-4 py-3 font-medium uppercase transition duration-200 bg-white rounded text-navy-blue hover:bg-golden hover:text-white">
                                        Checkout
                                        </x-marybutn>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
