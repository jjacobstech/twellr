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
        $this->toast('Item removed from cart!', 'success');
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

<div class="h-full bg-gray-100">
    @if (session('checkout_success'))
        @script
            <script>
                showNotification('Checkout Successful', 'Your order has been placed and is being processed. 🙂', 'success', 5)
            </script>
        @endscript
    @endif
    <div class="container mx-auto mt-10">
        <div class="flex shadow-md my-10">
            <div class="w-3/4 bg-white px-10 py-10">
                <div class="flex justify-between border-b pb-8">
                    <h1 class="font-semibold text-2xl">Shopping Cart</h1>
                    <h2 class="font-semibold text-2xl">{{ count($this->cartItems) }} Items</h2>
                </div>
                <div class="flex mt-10 mb-5">
                    <h3 class="font-semibold text-gray-600 text-xs uppercase w-2/5">Product Details</h3>
                    <h3 class="font-semibold text-center text-gray-600 text-xs uppercase w-1/5 text-center">Quantity
                    </h3>
                    <h3 class="font-semibold text-center text-gray-600 text-xs uppercase w-1/5 text-center">Price</h3>
                    <h3 class="font-semibold text-center text-gray-600 text-xs uppercase w-1/5 text-right">Total</h3>
                </div>

                @forelse ($cartItems as $item)
                    <div class="flex items-center hover:bg-gray-100 -mx-8 px-6 py-5">
                        <div class="flex w-2/5">
                            <div class="w-20">
                                <img src="{{ asset('uploads/products/design-stack/' . $item->product->front_view) }}"
                                    alt="{{ $item->product->name }}">
                            </div>
                            <div class="flex flex-col justify-between ml-4 flex-grow">
                                <span class="font-bold text-gray-700">{{ $item->product->name }}</span>
                                <span class="text-red-500 text-xs">{{ strtoupper($item->size) }}</span>
                                <button wire:click="removeFromCart({{ $item->id }})"
                                    class="font-semibold hover:text-red-500 text-gray-500 text-xs">Remove</button>
                            </div>
                        </div>
                        <div class="flex justify-center w-1/5">
                            <svg wire:click="decrementQuantity({{ $item }})"
                                class="fill-current text-gray-600 w-3 cursor-pointer" viewBox="0 0 448 512">
                                <path
                                    d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                            </svg>

                            <input class="mx-2 border text-center w-8" type="text" value="{{ $item->quantity }}"
                                disabled>

                            <svg wire:click="incrementQuantity({{ $item }})"
                                class="fill-current text-gray-600 w-3 cursor-pointer" viewBox="0 0 448 512">
                                <path
                                    d="M416 208H272V32c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v176H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h176v176c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V272h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                            </svg>
                        </div>
                        <span class="text-center w-1/5 font-semibold text-sm">${{ $item->product->price }}</span>
                        <span
                            class="text-center w-1/5 font-semibold text-sm">${{ $item->product->price * $item->quantity }}</span>
                    </div>
                @empty
                    <div class="py-6 text-center">
                        <p class="text-gray-500">Your cart is currently empty.</p>
                        <a href="{{ route('market.place') }}" class="text-blue-500 hover:underline">Continue
                            Shopping</a>
                    </div>
                @endforelse

                <a href="{{ route('market.place') }}" class="flex font-semibold text-indigo-600 text-sm mt-10">
                    <svg class="fill-current mr-2 text-indigo-600 w-4" viewBox="0 0 448 512">
                        <path
                            d="M134.059 296H436.059c21.38 0 32.09-25.9 17.06-40.94l-175.1-171.59c-15.23-14.92-40.85-2.52-40.85 16.36V296z" />
                    </svg>
                    Continue Shopping
                </a>
            </div>

            <div id="summary" class="w-1/4 px-8 py-10">
                <h1 class="font-semibold text-2xl border-b pb-8">Order Summary</h1>
                <div class="mt-8">
                    <div class="flex font-semibold justify-between py-6 text-sm uppercase">
                        <span>Subtotal</span>
                        <span>${{ $totalPrice }}</span>
                    </div>
                    <div>
                        <label class="font-semibold inline-block mb-3 text-sm uppercase">Shipping</label>
                        <select class="block p-2 text-gray-600 w-full text-sm">
                            <option>Standard shipping - Free</option>
                        </select>
                    </div>
                    <div class="py-10">
                        <label for="address" class="font-semibold inline-block mb-3 text-sm uppercase">Shipping
                            Address</label>
                        <textarea wire:model="address" id="address" class="block p-2 text-gray-600 w-full text-sm" rows="3"></textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>
                    <div class="border-t mt-8">
                        <div class="flex font-semibold justify-between py-6 text-sm uppercase">
                            <span>Total cost</span>
                            <span>${{ $totalPrice }}</span>
                        </div>
                        <button wire:click="checkout"
                            class="bg-indigo-500 font-semibold hover:bg-indigo-600 py-3 text-sm text-white uppercase w-full">
                            Checkout
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
