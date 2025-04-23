<?php

use App\Models\Checkout;
use Mary\Traits\Toast;
use App\Models\Product;
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

    public function mount()
    {
        $this->address = auth()->user()->address;
        $settings = AdminSetting::first();
        $this->shipping_fee = $settings ? $settings->shipping_fee : 0;
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

