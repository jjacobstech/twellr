<?php

use Mary\Traits\Toast;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Notification;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    use Toast;

    public $cartItems;
    public $totalPrice = 0;
    public $address;

    public function mount()
    {
        $this->address = auth()->user()->address;
        $this->loadCartItems();
    }

    public function loadCartItems()
    {
        $this->cartItems = Cart::where('user_id', Auth::id())->with('product')->get();
        $this->calculateTotalPrice();
    }

    public function calculateTotalPrice()
    {
        $this->totalPrice = $this->cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }

    public function incrementQuantity(Cart $cartItem)
    {
        $cartItem->increment('quantity');
        $this->loadCartItems();
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
                    'address' => ['required', 'min:10'],
                ],
                [
                    'address.required' => 'Delivery address cannot be empty',
                    'address.min' => 'Delivery address must be at least 10 characters',
                ],
            );

            foreach ($this->cartItems as $cartItem) {
                $product = $cartItem->product;
                $transaction = Transaction::create([
                    'user_id' => $product->user_id,
                    'buyer_id' => Auth::id(),
                    'amount' => $product->price * $cartItem->quantity,
                    'transaction_type' => 'sales',
                    'status' => 'pending',
                ]);
                $transaction ? '' : abort(500, 'Something Went Wrong During Transaction Creation');

                $purchase = Purchase::create([
                    'transactions_id' => $transaction->id,
                    'user_id' => $product->user_id,
                    'buyer_id' => Auth::id(),
                    'address' => $orderData->address,
                    'product_id' => $product->id,
                    'delivery_status' => 'pending',
                    'phone_no' => Auth::user()->phone_no,
                    'quantity' => $cartItem->quantity,
                    'size' => $cartItem->size,
                ]);
                $purchase ? '' : abort(500, 'Something Went Wrong During Purchase Creation');

                $seller = $product->user;
                $buyerName = Auth::user()->firstname . ' ' . Auth::user()->lastname;

                Notification::create([
                    'user_id' => $seller->id,
                    'title' => $product->name . ' design has been ordered',
                    'message' => "$buyerName has purchased " . $cartItem->quantity . ' units of ' . $product->name,
                    'type' => 'sales',
                ]);
            }

            Cart::where('user_id', Auth::id())->delete();
            session()->flash('checkout_success', true);
            $this->redirectIntended(route('market.place'), true);
        }
    }
}; ?>

<div class="w-full pb-20 h-screen bg-gray-100 overflow-y-scroll">
    <!-- Success Toast -->
    @if (session('checkout_success'))
        <div class="fixed top-4 right-4 z-50" x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-500">
            <div class="flex items-center justify-between p-4 bg-navy-blue text-white rounded-lg shadow-lg">
                <span class="font-bold">Checkout Successful</span>
                <button @click="show = false" class="ml-4">
                    @svg('eva-close', 'h-5 w-5 text-red-500 hover:text-red-400')
                </button>
            </div>
        </div>
    @endif

       @if (session('remove_success'))
        <div class="fixed top-4 right-4 z-50" x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-500">
            <div class="flex items-center justify-between p-4 bg-navy-blue text-white rounded-lg shadow-lg">
                <span class="font-bold">Item Removed</span>
                <button @click="show = false" class="ml-4">
                    @svg('eva-close', 'h-5 w-5 text-red-500 hover:text-red-400')
                </button>
            </div>
        </div>
    @endif

    <!-- Main Container -->
    <div class="max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-700 mb-6">Shopping Cart</h1>

        <!-- Cart Container -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Cart Items Section -->
            <div class="w-full lg:w-2/3 bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Cart Header -->
                <div class="p-4 sm:p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <h2 class="text-lg font-semibold text-gray-700">Your Items</h2>
                    <p class="text-sm text-gray-500 mt-1 sm:mt-0">{{ count($cartItems) }} Items</p>
                </div>

                <!-- Empty Cart Message -->
                @if(count($cartItems) == 0)
                    <div class="p-8 text-center">
                        <p class="text-gray-500 mb-4">Your cart is currently empty.</p>
                        <a href="{{ route('market.place') }}" class="inline-flex items-center text-navy-blue hover:text-golden font-medium">
                            @svg('eva-arrow-back', 'w-4 h-4 mr-2')
                            Browse Products
                        </a>
                    </div>
                @else
                    <!-- Cart Items -->
                    <div class="divide-y">
                        @foreach($cartItems as $item)
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
                                            <p class="text-sm font-medium text-gray-700 mt-1">${{ $item->product->price }}</p>
                                        </div>
                                    </div>

                                    <!-- Mobile Controls -->
                                    <div class="flex items-center justify-between mt-2">
                                        <!-- Quantity Controls -->
                                        <div class="flex items-center border rounded-md">
                                            <button wire:click="decrementQuantity({{ $item }})" class="px-3 py-1">
                                                <svg class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 448 512">
                                                    <path d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                </svg>
                                            </button>
                                            <span class="px-3 py-1 border-x text-sm">{{ $item->quantity }}</span>
                                            <button wire:click="incrementQuantity({{ $item }})" class="px-3 py-1">
                                                <svg class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 448 512">
                                                    <path d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Item Total -->
                                        <div class="text-right">
                                            <p class="font-medium text-gray-800">${{ $item->product->price * $item->quantity }}</p>
                                            <button wire:click="removeFromCart({{ $item->id }})" class="text-xs text-red-600 mt-1">
                                                Remove
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
                                            <button wire:click="removeFromCart({{ $item->id }})" class="text-xs text-gray-600 hover:text-red-600 mt-1">
                                                Remove
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="w-1/5 flex justify-center">
                                        <div class="flex items-center border rounded-md">
                                            <button wire:click="decrementQuantity({{ $item }})" class="px-2 py-1">
                                                <svg class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 448 512">
                                                    <path d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                </svg>
                                            </button>
                                            <span class="px-3 py-1 border-x text-sm text-black">{{ $item->quantity }}</span>
                                            <button wire:click="incrementQuantity({{ $item }})" class="px-2 py-1">
                                                <svg class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 448 512">
                                                    <path d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Unit Price -->
                                    <div class="w-1/5 text-center">
                                        <span class="text-sm font-medium text-gray-700">${{ $item->product->price }}</span>
                                    </div>

                                    <!-- Item Total -->
                                    <div class="w-1/5 text-right">
                                        <span class="text-sm font-medium text-gray-800">${{ $item->product->price * $item->quantity }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Continue Shopping Button -->
                    <div class="p-4 sm:p-6 border-t">
                        <a href="{{ route('market.place') }}" class="inline-flex items-center text-navy-blue hover:text-golden font-medium">
                            @svg('eva-arrow-back', 'w-4 h-4 mr-2')
                            Continue Shopping
                        </a>
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
                                <option>Standard shipping - Free</option>
                            </select>
                        </div>

                        <!-- Shipping Address -->
                        <div>
                            <label for="address" class="block mb-2 text-sm font-medium uppercase">Shipping Address</label>
                            <textarea
                                wire:model="address"
                                id="address"
                                class="w-full p-3 rounded bg-white text-gray-700 text-sm"
                                rows="3"
                                placeholder="Enter your shipping address"></textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <!-- Total Cost -->
                        <div class="pt-4 border-t border-blue-800">
                            <div class="flex justify-between font-medium">
                                <span class="uppercase text-sm">Total cost</span>
                                <span>${{ $totalPrice }}</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button
                            wire:click="checkout"
                            class="w-full py-3 px-4 bg-white text-navy-blue font-medium uppercase rounded hover:bg-golden hover:text-white transition duration-200">
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
