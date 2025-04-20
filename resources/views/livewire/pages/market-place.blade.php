<?php

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\State;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\Product;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\Transaction;
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
    public string $country = '';
    public $state;
    public string $material;

    public function mount()
    {
        $this->productCategory = request()->slug;
        $this->address = auth()->user()->address;
        $this->productFilter = request()->filter;
        $this->products = Product::all();
        $this->materials = Material::all();
        $this->countries = Country::all();
    }

    public function getStates($countryId = null)
    {
        return $this->states = State::where('country_id', '=', $countryId)->get();
    }
    public function addToCart($id)
    {
        $product = Product::where('id', '=', $id)->first();

        $itemExists = Cart::where('product_id', '=', $product->id)->where('user_id', '=', Auth::id())->exists();
        if ($itemExists) {
            $this->warning('Cannot Add', 'This item is already in the cart');
        } elseif (!$itemExists) {
            $addToCart = Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ]);
            if (!$addToCart) {
                abort(500, 'Something went wrong but we are working on it');
            }
            $this->success('Added', 'Item added to cart!');
        }
    }

    public function addToWishlist($id)
    {
        dd($id);
    }

    public function generateUniqueReferenceNumber()
    {
        $prefix = 'TRX';
        do {
            $timestamp = now()->format('YmdHis');
            $randomString = strtoupper(Str::random(6));
            $reference_no = $prefix . $timestamp . $randomString . Auth::id();

            $exists = Transaction::where('ref_no', '=', $reference_no)->get();
        } while ($exists);

        return $reference_no;
    }

    public function orderModal($id)
    {
        $this->order = Product::where('id', '=', $id)->first();
        $this->modal = true;
        $this->order->user_id == Auth::id() ? ($this->owner = true) : ($this->owner = false);
    }

    public function orderProduct() {
$this->marketplace = false;
$this->checkout = true;
    }

    public function checkout()
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
                ],
                [
                    'size.required' => 'Size Not Selected',
                    'address.required' => 'Address cannot be empty',
                    'country.required' => 'Country Not Selected',
                    'state.required' => 'State Not Selected',
                    'material.required' => 'Material Not Selected',
                ],
            );

            $ref_no = $this->generateReferenceNumber();

            $transaction = Transaction::create([
                'user_id' => $this->order->user_id,
                'buyer_id' => Auth::id(),
                'amount' => $this->order->price,
                'transaction_type' => 'sales',
                'ref_no' => $ref_no,
                'status' => 'pending',
            ]);
            $transaction ? '' : abort(500, 'Something Went Wrong, We Are Working On It');

            $purchase = Purchase::create([
                'transactions_id' => $transaction->id,
                'user_id' => $this->order->user_id,
                'buyer_id' => Auth::id(),
                'product_id' => $this->order->id,
                'delivery_status' => 'pending',
                'phone_no' => Auth::user()->phone_no,
                'address' => $orderInfo->address,
                'amount',
                'location' => "$orderInfo->address ,$orderInfo->state, $orderInfo->country",
                'size' => $orderInfo->size,
                'product_name' => $this->order->name,
                'product_category' => $this->order->category,
                'material' => $orderInfo->material,
            ]);
            $user = Auth::user()->firstname . ' ' . Auth::user()->lastname;
            $purchase ? '' : abort(500, 'Something Went Wrong, We Are Working On It');

            Notification::create([
                'user_id' => $this->order->user_id,
                'title' => $this->order->name . ' design has been ordered',
                'message' => "$user has purchase " . $this->order->name,
                'type' => 'sales',
            ]);
            $this->success('Order Successful', 'Your order is on its way', icon: 'eva-car', redirectTo: route('market.place'));
            //    $this->success('Order Successful','Your order is on its way',redirectTo:route('market.place'));
        }
    }
}; ?>

<div>
    <div wire:show="marketplace" class="flex w-[100%] space-x-1">

        <x-market-place-sidebar class="" />
        <div class="relative bg-white w-screen pb-8 md:pb-0 md:w-[72%] lg:w-[80%] md:h-screen py-4 overflow-y-scroll">
            <div x-cloak="display:hidden"
                class="relative grid w-full h-full gap-5 mb-1 px-5 pt-1 md:h-screen overflow-y-scroll lg:hidden md:grid-cols-2 sm:grid-cols-2">
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
                            {{-- <div class="flex-none w-full mt-2 text-sm font-medium text-black ">
                                @if ($order)
                                    In Stock
                                @endif
                            </div> --}}
                        </div>

                        <!-- sizes -->
                        <div class="grid items-baseline mt-4  text-gray-700 md:mb-1">
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
                            <x-input-error :messages="$errors->get('size')" class="mt-1 " />

                            {{-- <a href="#" class="hidden ml-auto text-sm text-gray-500 underline md:block">Size
                                    Guide
                                </a> --}}
                        </div>

                        <!-- Material -->
                        <div class="w-full md:w-100 my-3">
                            <x-input-label class="font-extrabold bg-white " for="email" :value="__('Select Material')" />
                            <select wire:model.live="material" id="status"
                                class="block w-full border border-gray-500  rounded-md shadow-sm py-2 px-3 focus:outline-none focus:navy-blue  text-black focus:navy-blue sm:text-sm">
                                <option value="">Select a material</option>
                                @forelse ($materials as $material)
                                    <option value="{{ $material->id }}">{{ ucfirst($material->name) }}</option>
                                @empty
                                    <option value=""> . . . </option>
                                @endforelse
                            </select>
                            <x-input-error :messages="$errors->get('material')" class="mt-1" />

                        </div>

                        <!-- Country -->
                        @if (!isset(Auth::user()->country_id))
                            <div class="w-full md:w-100 my-3">
                                <x-input-label class="font-extrabold bg-white " for="country" :value="__('Country')" />
                                <select wire:model.live="country" id="country"
                                    class="block w-full border border-gray-500  rounded-md shadow-sm py-2 px-3 focus:outline-none focus:navy-blue  text-black focus:navy-blue sm:text-sm">
                                    <option value="" @click="$wire.getStates()">Select a Country</option>
                                    @forelse ($countries as $country)
                                        <option @click="$wire.getStates({{ $country->id }})"
                                            value="{{ $country->id }}">{{ $country->name }}</option>
                                    @empty
                                        <option value=""> . . . </option>
                                    @endforelse
                                </select>
                                <x-input-error :messages="$errors->get('country')" class="mt-1 " />
                            </div>
                        @endif


                        <!-- State -->
                        @if (!isset(Auth::user()->state_id))
                            <div class="w-full md:w-100 my-3">
                                <x-input-label class="font-extrabold bg-white " for="state" :value="__('State')" />
                                <select wire:model.live="state" id="state"
                                    class="block w-full border border-gray-500  rounded-md shadow-sm py-2 px-3 focus:outline-none focus:navy-blue  text-black focus:navy-blue sm:text-sm">
                                    <option value="">Select a State</option>
                                    @forelse ($states as $state)
                                        <option value="{{ $state->id }}">{{ ucfirst($state->name) }}</option>

                                    @empty


                                        <option value=""> . . . </option>
                                    @endforelse
                                </select>
                                <x-input-error :messages="$errors->get('state')" class="mt-1" />
                            </div>
                        @endif


                        <!-- Address -->
                        <div class="relative my-3 w-100">
                            <x-input-label class="font-extrabold bg-white " for="email" :value="__('Delivery Address')" />

                            <div class="w-full ">
                                <x-text-input wire:model="address" id="address"
                                    class="block w-full mt-2 text-black" type="text" name="email" required
                                    autocomplete="address" />
                                <x-input-error :messages="$errors->get('address')" class="mt-1" />
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
    <div wire:show="checkout" class="w-full pb-20 h-screen bg-gray-100 overflow-y-scroll">
        <!-- Main Container -->
        <div class="max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-4">
            <h1 class="text-2xl font-bold text-gray-700 mb-6">Checkout</h1>

            <!-- Checkout Container -->
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Checkout Items Section -->
                <div class="w-full lg:w-2/3 bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Checkout Header -->
                    <div
                        class="p-4 sm:p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <h2 class="text-lg font-semibold text-gray-700">Your Items</h2>
                        <p class="text-sm text-gray-500 mt-1 sm:mt-0">{{ count($cartItems) }} Items</p>
                    </div>

                    <!-- Empty Checkout Message -->
                    @if (count($cartItems) == 0)
                        <div class="p-8 text-center">
                            <p class="text-gray-500 mb-4">Your cart is currently empty.</p>
                            <a href="{{ route('market.place') }}"
                                class="inline-flex items-center text-navy-blue hover:text-golden font-medium">
                                @svg('eva-arrow-back', 'w-4 h-4 mr-2')
                                Browse Products
                            </a>
                        </div>
                    @else
                        <!-- Checkout Items -->
                        <div class="divide-y">
                            @foreach ($cartItems as $item)
                                <div class="p-4 sm:p-6">
                                    <!-- Mobile View (Stacked) -->
                                    <div class="flex flex-col sm:hidden">
                                        <div class="flex mb-4">
                                            <!-- Product Image -->
                                            <div class="w-20 h-20 flex-shrink-0">
                                                <img class="w-full h-full object-cover rounded"
                                                    src="{{ asset('uploads/products/design-stack/' . $item->product->front_view) }}"
                                                    alt="{{ $item->product->name }}">
                                            </div>

                                            <!-- Product Details -->
                                            <div class="ml-4 flex-grow">
                                                <h3 class="font-medium text-gray-800">{{ $item->product->name }}</h3>
                                                <p class="text-xs text-red-500 mt-1">{{ strtoupper($item->size) }}</p>
                                                <p class="text-sm font-medium text-gray-700 mt-1">
                                                    ${{ $item->product->price }}</p>
                                            </div>
                                        </div>

                                        <!-- Mobile Controls -->
                                        <div class="flex items-center justify-between mt-2">
                                            <!-- Quantity Controls -->
                                            <div class="flex items-center border rounded-md">
                                                <button wire:click="decrementQuantity({{ $item }})"
                                                    class="px-3 py-1">
                                                    <svg class="w-3 h-3 text-gray-600" fill="currentColor"
                                                        viewBox="0 0 448 512">
                                                        <path
                                                            d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                    </svg>
                                                </button>
                                                <span
                                                    class="px-3 py-1 border-x text-sm text-black">{{ $item->quantity }}</span>
                                                <button wire:click="incrementQuantity({{ $item }})"
                                                    class="px-3 py-1">
                                                    <svg class="w-3 h-3 text-gray-600" fill="currentColor"
                                                        viewBox="0 0 448 512">
                                                        <path
                                                            d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tablet/Desktop View -->
                                    <div class="hidden sm:flex items-center">
                                        <!-- Product Image & Details -->
                                        <div class="flex items-center w-2/5">
                                            <div class="w-20 h-20 flex-shrink-0">
                                                <img class="w-full h-full object-cover rounded"
                                                    src="{{ asset('uploads/products/design-stack/' . $item->product->front_view) }}"
                                                    alt="{{ $item->product->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <h3 class="font-medium text-gray-800">{{ $item->product->name }}</h3>
                                                <p class="text-xs text-red-500 mt-1">{{ strtoupper($item->size) }}</p>
                                                <button wire:click="removeFromCheckout({{ $item->id }})"
                                                    class="text-xs text-gray-600 hover:text-red-600 mt-1">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Quantity Controls -->
                                        <div class="w-1/5 flex justify-center">
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
                                                    class="px-3 py-1 border-x text-sm text-black">{{ $item->quantity }}</span>
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
                                        <div class="w-1/5 text-center">
                                            <span
                                                class="text-sm font-medium text-gray-700">${{ $item->product->price }}</span>
                                        </div>

                                        <!-- Item Total -->
                                        <div class="w-1/5 text-right">
                                            <span
                                                class="text-sm font-medium text-gray-800">${{ $item->product->price * $item->quantity }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Continue Shopping Button -->
                        <div class="p-4 sm:p-6 border-t">
                            <span class="inline-flex items-center text-navy-blue hover:text-golden font-medium">
                                @svg('eva-arrow-back', 'w-4 h-4 mr-2')
                                Continue Shopping
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Order Summary Section -->
                <div class="w-full lg:w-1/3 mt-6 lg:mt-0">
                    <div class="bg-navy-blue rounded-lg shadow-md text-white overflow-hidden">
                        <div class="p-6 border-b border-blue-800">
                            <h2 class="text-xl font-semibold">Order Summary</h2>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Subtotal -->
                            <div class="flex justify-between">
                                <span class="font-medium uppercase text-sm">Subtotal</span>
                                <span>${{ $totalPrice }}</span>
                            </div>

                            <!-- Shipping Option -->
                            <div>
                                <label class="block mb-2 text-sm font-medium uppercase">Shipping</label>
                                <select class="w-full p-2 rounded bg-white text-gray-700 text-sm">
                                    <option>Standard Shipping - {{ $shipping_fee ? "$$shipping_fee" : 'Free' }}
                                    </option>
                                </select>
                            </div>

                            <!-- Shipping Address -->
                            <div>
                                <label for="address" class="block mb-2 text-sm font-medium uppercase">Shipping
                                    Address</label>
                                <textarea wire:model.live="address" id="address" class="w-full p-3 rounded bg-white text-gray-700 text-sm"
                                    rows="3" placeholder="Enter your shipping address"></textarea>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            <!-- Total Cost -->
                            <div class="pt-4 border-t border-blue-800">
                                <div class="flex justify-between font-medium">
                                    <span class="uppercase text-sm">Total cost</span>
                                    <span>${{ $totalPrice + $shipping_fee }}</span>
                                </div>
                            </div>

                            <!-- Checkout Button -->
                            <button wire:click="checkout"
                                class="w-full py-3 px-4 bg-white text-navy-blue font-medium uppercase rounded hover:bg-golden hover:text-white transition duration-200">
                                Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
