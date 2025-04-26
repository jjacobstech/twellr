<?php

use Carbon\Carbon;
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

    public $products;
    public $productCategory = '';
    public $productFilter = '';
    public bool $modal = false;
    public bool $marketplace = true;
    public bool $checkout = false;
    public $order;
    public $address;
    public string $size = '';
    public bool $owner;
    public $cartItem;
    public $added;
    public $exists;
    public $materials;
    public $countries = [];
    public $states = [];
    public $country;
    public string $material;
    public $totalPrice = 0;
    public $shipping_fee;
    public int $quantity = 1;
    public $subTotal;
    public $itemTotal;
    public $newPrice;
    public $oldPrice;
    public $state;
    public string $phone_no;

    public function mount()
    {
        $this->productCategory = request()->slug;
        $this->address = auth()->user()->address;
        $this->phone_no = auth()->user()->phone_no;
        $this->productFilter = request()->filter;
        $this->products = Product::all();
        $this->materials = Material::all();
        $this->countries = Country::all();
        $this->shipping_fee = AdminSetting::first()->shipping_fee;
        $this->country = Auth::user()->country_id ?? '';
        $this->states = Auth::user()->state_id ? State::where('country_id', Auth::user()->country_id)->get() : [];
        $this->state = Auth::user()->state_id;
        $this->order = Product::where('id', 1)->first();
    }
    public function incrementQuantity()
    {
        $this->quantity++;
        $this->subTotal = $this->itemTotal * $this->quantity;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
    }
    public function decrementQuantity()
    {
        $this->subTotal = $this->subTotal - $this->subTotal / $this->quantity;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
        $this->quantity == 1 ? '' : $this->quantity--;
    }
    public function addMaterial($price = 0)
    {
        $this->newPrice = $price;
        $this->itemTotal = $this->itemTotal - $this->oldPrice;
        $this->oldPrice = $this->newPrice;
        $this->itemTotal = $this->itemTotal + $this->newPrice;
        $this->subTotal = $this->itemTotal * $this->quantity;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
    }
    public function getStates($countryId = null)
    {
        if (!$countryId) {
            return [];
        }

        $this->states = State::where('country_id', $countryId)->get();
        return $this->states;
    }
    public function addToCart($id)
    {
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
    }

    public function addToWishlist($id)
    {
        dd($id);
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

    public function orderModal($id)
    {
        $this->order = Product::where('id', '=', $id)->first();
        $this->modal = true;
        $this->order->user_id == Auth::id() ? ($this->owner = true) : ($this->owner = false);
    }

    public function orderProduct()
    {
        $this->marketplace = false;
        $this->checkout = true;
        $this->itemTotal = $this->order->price;
        $this->subTotal = $this->itemTotal * $this->quantity;
        $this->totalPrice = $this->subTotal + $this->shipping_fee;
    }

    public function completeCheckout()
    {
        if (empty(Auth::user()->phone_no)) {
            session()->put('was_redirected', true);
            $this->redirectIntended(route('settings'), true);
        } else {
            $orderInfo = (object) $this->validate(
                [
                    'size' => ['required'],
                    'material' => ['required'],
                    'country' => ['required'],
                    'state' => ['required'],
                    'address' => ['required', 'min:10'],
                    'phone_no' => ['required'],
                ],
                [
                    'size.required' => $this->warning('Size Not Selected'),
                    'address.required' => $this->warning('Address Not Selected'),
                    'country.required' => $this->warning('Country Not Selected'),
                    'state.required' => $this->warning('State Not Selected'),
                    'material.required' => $this->warning('Material Not Selected'),
                    'phone_no.required' => $this->warning('Mobile Number Is Required For Delivery'),
                ],
            );

            if ($this->totalPrice > Auth::user()->wallet_balance) {
                return $this->error("Low Balance $" . Auth::user()->wallet_balance, 'Your wallet balance is low. Add funds to continue');
            } else {
                $ref_no = $this->generateReferenceNumber();
                $transaction = Transaction::create([
                    'user_id' => $this->order->user_id,
                    'buyer_id' => Auth::id(),
                    'amount' => $this->order->price,
                    'transaction_type' => 'sales',
                    'ref_no' => $ref_no,
                    'status' => 'pending',
                ]);

                if (!$transaction) {
                    $this->withdrawalModal = false;
                    return $this->error('Withdrawal Error - Transaction Registration Service', 'An error has occured, but we are working on it');
                }

                $purchase = Purchase::create([
                    'transactions_id' => $transaction->id,
                    'user_id' => $this->order->user_id,
                    'buyer_id' => Auth::id(),
                    'product_id' => $this->order->id,
                    'delivery_status' => 'pending',
                    'phone_no' => $orderInfo->phone_no,
                    'address' => $orderInfo->address,
                    'amount' => $this->totalPrice,
                    'location' => "$orderInfo->state, $orderInfo->country",
                    'size' => $orderInfo->size,
                    'product_name' => $this->order->name,
                    'product_category' => $this->order->category,
                    'material' => $orderInfo->material,
                    'quantity' => $this->quantity,
                ]);

                if (!$purchase) {
                    Transaction::where('id', '=', $transaction->id)->delete();
                    $this->withdrawalModal = false;
                    return $this->error('Withdrawal Error - Purchase Registration Service', 'An error has occured, but we are working on it');
                }

                $buyer = User::where('id', '=', Auth::id())->first();
                $buyer->wallet_balance = $buyer->wallet_balance - $this->totalPrice;
                $deducted = $buyer->save();

                if (!$deducted) {
                    Transaction::where('id', '=', $transaction->id)->delete();
                    Purchase::where('id', '=', $purchase->id)->delete();
                    $this->withdrawalModal = false;
                    return $this->error('Withdrawal Error - Buyer Deduction Service', 'An error has occured, but we are working on it');
                }

                $creative = User::where('id', '=', $this->order->user_id)->first();
                $creative->wallet_balance = $creative->wallet_balance + $this->order->price;
                $deposited = $creative->save();

                if (!$deposited) {
                    Transaction::where('id', '=', $transaction->id)->delete();
                    Purchase::where('id', '=', $purchase->id)->delete();
                    $buyer->wallet_balance = $buyer->wallet_balance + $this->totalPrice;
                    $deducted = $buyer->save();
                    $this->withdrawalModal = false;
                    return $this->error('Withdrawal Error - Creative Deposit Service', 'An error has occured, but we are working on it');
                }

                $buyer_name = Auth::user()->firstname . ' ' . Auth::user()->lastname;

                $notification = Notification::create([
                    'user_id' => $this->order->user_id,
                    'title' => $this->order->name . ' design has been ordered',
                    'message' => "$buyer_name has purchase " . $this->order->name,
                    'type' => 'sales',
                ]);

                if (!$notification) {
                    Transaction::where('id', '=', $transaction->id)->delete();
                    Purchase::where('id', '=', $purchase->id)->delete();
                    $buyer->wallet_balance = $buyer->wallet_balance + $this->totalPrice;
                    $deducted = $buyer->save();
                    $creative->wallet_balance = $creative->wallet_balance - $this->order->price;
                    $deposited = $creative->save();
                    $this->withdrawalModal = false;
                    return $this->error('Withdrawal Error - Notification Service', 'An error has occured, but we are working on it');
                }

                $this->success('Order Successful', 'Your order is on its way', redirectTo: route('market.place'));
            }
        }
    }
}; ?>

<div class="h-full">

    <!-- Marketplace -->
    <div wire:show="marketplace" class="flex w-[100%] space-x-1 h-full">

        <x-market-place-sidebar class="" />
        <div class="relative bg-white w-screen pb-8 md:pb-0 md:w-[72%] lg:w-[80%] md:h-screen py-4 overflow-y-scroll">
            <div x-cloak="display:hidden"
                class="relative grid w-full gap-5 px-5 pt-1 mb-16 overflow-y-scroll md:h-screen lg:hidden md:grid-cols-2 sm:grid-cols-2">
                @foreach ($products as $product)
                    <x-product-card :$product />
                @endforeach
            </div>

            <div x-cloak="display:hidden"
                class="relative hidden w-full lg:h-screen gap-5 px-5 pt-1 lg:overflow-y-scroll lg:grid md:grid-cols-4 mb-[115px]">
                @foreach ($products as $product)
                    <x-product-card :$product />
                @endforeach

            </div>
            <div x-data="{ isOpen: @entangle('modal').live }" x-show="isOpen" x-cloak='display:none' x-transition.opacity
                class="fixed inset-0 z-50 w-screen h-full px-5 py-16 sm:py-12 md:py-12 md:px-20 lg:py-12 bg-black/40 backdrop-blur-sm pb-26">
                <div class="grid h-full lg:flex justify-evenly rounded-xl md:flex-row"
                    @click.away="$wire.modal = false">
                    <div class=" object-fit-contain lg:w-[75%] carousel rounded-t-xl md:rounded-none lg:rounded-l-xl">
                        <div class="relative w-full carousel-item" id="front-view">
                            <img src="@if ($order) {{ asset('uploads/products/design-stack/' . $order->front_view) }} @endif"
                                alt="shopping image" class="object-cover w-full h-full ">

                            <div
                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                <a href="#side-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                <a href="#back-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                            </div>
                        </div>
                        <div class="relative w-full carousel-item" id="back-view">
                            <img src="@if ($order) {{ asset('uploads/products/design-stack/' . $order->back_view) }} @endif"
                                alt="shopping image" class="object-cover w-full h-full ">
                            <div
                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                <a href="#front-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                <a href="#side-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                            </div>
                        </div>
                        <div class="relative w-full carousel-item" id="side-view">
                            <img src="@if ($order) {{ asset('uploads/products/design-stack/' . $order->side_view) }} @endif"
                                alt="shopping image" class="object-cover w-full h-full ">
                            <div
                                class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
                                <a href="#back-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❮</a>
                                <a href="#front-view"
                                    class="text-white bg-transparent border-white btn btn-circle hover:bg-navy-blue">❯</a>
                            </div>
                        </div>
                    </div>

                    <form
                        class="lg:w-[25%] px-6 md:px-10 lg:px-6 bg-white lg:rounded-r-xl space-y-3 overflow-y-scroll py-5">
                        <!-- design info -->
                        <div class="flex flex-wrap mt-5 ">
                            <h1 class="flex-auto text-xl font-semibold text-black">
                                @if ($order)
                                    {{ $order->name }}
                                @endif
                            </h1>
                            <div class="text-xl font-semibold text-black">
                                @if ($order)
                                    {{ $order->price }}
                                @endif
                            </div>
                            <div class="flex-none w-full mt-2 font-extrabold text-black text-md ">
                                @if ($order)
                                    {{ $order->category }}
                                @endif
                            </div>
                        </div>

                        <!-- Order Button -->
                        <div class="flex justify-center mb-4 text-sm font-medium">
                            @if ($owner)
                                <x-mary-button disabled label="Order"
                                    class="bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                    wire:click="orderProduct" spinner />
                            @else
                                <x-mary-button label="Order"
                                    class="bg-[#001f54] text-white hover:bg-golden hover:border-golden"
                                    wire:click="orderProduct" spinner />
                            @endif
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Checkout -->
    <div wire:show="checkout" class="w-full h-screen pb-20 overflow-y-scroll bg-gray-100">
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
                                        <p class="mt-1 text-sm text-red-500">{{ strtoupper($order->size) }}</p>
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
                                        <select wire:model.live="material" id="status"
                                            class="block w-full border border-gray-300 rounded-lg py-2.5 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-navy-blue focus:border-navy-blue text-gray-800 text-sm">
                                            <option wire:click='addMaterial' value="">Select a material
                                            </option>
                                            @forelse ($materials as $material)
                                                <option wire:click='addMaterial({{ $material->price }})'
                                                    value="{{ $material->id }}">
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
                                        <div class="flex justify-between p-2 rounded-lg bg-gray-50">
                                            <label class="flex flex-col items-center justify-center px-2 text-center">
                                                <input type="radio" class="w-4 h-4 mb-1 accent-navy-blue"
                                                    name="size" wire:model='size' value="xs">
                                                <span class="text-xs font-medium text-gray-800">XS</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center px-2 text-center">
                                                <input type="radio" class="w-4 h-4 mb-1 accent-navy-blue"
                                                    name="size" wire:model='size' value="s">
                                                <span class="text-xs font-medium text-gray-800">S</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center px-2 text-center">
                                                <input type="radio" class="w-4 h-4 mb-1 accent-navy-blue"
                                                    name="size" wire:model='size' value="m">
                                                <span class="text-xs font-medium text-gray-800">M</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center px-2 text-center">
                                                <input type="radio" class="w-4 h-4 mb-1 accent-navy-blue"
                                                    name="size" wire:model='size' value="l">
                                                <span class="text-xs font-medium text-gray-800">L</span>
                                            </label>
                                            <label class="flex flex-col items-center justify-center px-2 text-center">
                                                <input type="radio" class="w-4 h-4 mb-1 accent-navy-blue"
                                                    name="size" wire:model='size' value="xl">
                                                <span class="text-xs font-medium text-gray-800">XL</span>
                                            </label>
                                        </div>
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
                                    <button
                                        class="flex items-center justify-center w-full py-3 mt-2 font-medium text-white transition duration-200 rounded-lg bg-navy-blue hover:bg-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Add to Cart
                                    </button>
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


                                <!-- Material -->
                                <div class=" w-[30%]">
                                    <x-input-label class="font-extrabold bg-white " for="material"
                                        :value="__('Material')" />
                                    <select wire:model.live="material" id="status"
                                        class="w-full px-3 py-2 text-black border-gray-500 rounded-md shadow-sm blockborder focus:outline-none focus:navy-blue sm:text-sm">
                                        <option wire:click='addMaterial' value="">Select a material</option>
                                        @forelse ($materials as $material)
                                            <option wire:click='addMaterial({{ $material->price }})'
                                                value="{{ $material->id }}">
                                                {{ ucfirst($material->name) }} - ${{ $material->price }}</option>
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
                                    <div class="flex space-x-2">

                                        <label class="text-center text-black">

                                            <input type="radio"
                                                class="flex items-center justify-center w-6 h-6 bg-white rounded-lg accent-navy-blue "
                                                name="size" wire:model='size' value="xs">XS
                                        </label>
                                        <label class="text-center text-black">
                                            <input type="radio"
                                                class="flex items-center justify-center w-6 h-6 accent-navy-blue "
                                                name="size" wire:model='size' value="s">S
                                        </label>
                                        <label class="text-center text-black">
                                            <input type="radio"
                                                class="flex items-center justify-center w-6 h-6 accent-navy-blue "
                                                name="size" wire:model='size' value="m">M
                                        </label>
                                        <label class="text-center text-black">
                                            <input type="radio"
                                                class="flex items-center justify-center w-6 h-6 accent-navy-blue "
                                                name="size" wire:model='size' value="l">L
                                        </label>
                                        <label class="text-center text-black">
                                            <input type="radio"
                                                class="flex items-center justify-center w-6 h-6 accent-navy-blue "
                                                name="size" wire:model='size' value="xl">XL
                                        </label>
                                    </div>
                                    <x-input-error :messages="$errors->get('size') ? 'Size Not Selected' : ''" class="mt-1" />

                                    {{-- <a href="#" class="hidden ml-auto text-sm text-gray-500 underline md:block">Size
                                    Guide
                                </a> --}}
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
                        <span class="inline-flex items-center font-medium text-navy-blue hover:text-golden">
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



                            <!-- State -->
                            <div class="w-full my-3 md:w-100">
                                <x-input-label class="text-sm font-extrabold text-white uppercase" for="state"
                                    :value="__('State')" />
                                <select wire:model.live="state" id="state"
                                    class="block w-full px-3 py-2 text-black border border-gray-500 rounded shadow-sm focus:outline-none focus:navy-blue sm:text-sm">
                                    {{ Auth::user()->state_id }}
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
                                <x-input-error :messages="$errors->get('phone_no') ? 'Mobile Number Is Required For Delivery' : ''" class="mt-1" />
                            </div>

                            <!-- Shipping Address -->
                            <div>
                                <label for="address" class="block mb-2 text-sm font-medium uppercase">Shipping
                                    Address</label>
                                <textarea wire:model.live="address" id="address" class="w-full p-3 text-sm text-gray-700 bg-white rounded"
                                    rows="3" placeholder="Enter your shipping address"></textarea>
                                <x-input-error :messages="$errors->get('address') ? 'Address Not Selected' : ''" class="mt-1" />
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

