<?php

use Carbon\Carbon;
use App\Models\Cart;
use Mary\Traits\Toast;
use App\Models\Product;
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
    public $order;
    public $address;
    public string $size = '';
    public bool $owner;
    public $cartItem;
    public $added;
    public $exists;

    public function mount()
    {
        $this->productCategory = request()->slug;
        $this->address = auth()->user()->address;
        $this->productFilter = request()->filter;
        $this->products = Product::all();
    }
    public function addToCart($id)
    {
        $product = Product::where('id', '=', $id)->first();

        $itemExists = Cart::where('product_id', '=', $product->id)->exists();
        if ($itemExists) {
             $this->warning('Cannot Add',"This item is already in the cart");
        } elseif (!$itemExists) {
            $addToCart = Cart::create([
                'user_id' => $product->user_id,
                'product_id' => $product->id,
            ]);
            if (!$addToCart) {
                abort(500, 'Something went wrong but we are working on it');
            }
             $this->success('Added', 'Item added to cart!',);
        }
    }

    public function addToWishlist($id)
    {
        dd($id);
    }
    public function orderModal($id)
    {
        $this->order = Product::where('id', '=', $id)->first();
        $this->modal = true;
        $this->order->user_id == Auth::id() ? ($this->owner = true) : ($this->owner = false);
    }

    public function orderProduct()
    {
        if (empty(Auth::user()->phone_no)) {
            session()->put('was_redirected', true);
            $this->redirectIntended(route('settings'), true);
        } else {
            $order = (object) $this->validate(
                [
                    'size' => ['required'],
                    'address' => ['required', 'min:10'],
                ],
                [
                    'size.required' => 'Size Not Selected',
                    'address.required' => 'Address cannot be empty',
                ],
            );
            $transaction = Transaction::create([
                'user_id' => $this->order->user_id,
                'buyer_id' => Auth::id(),
                'amount' => $this->order->price,
                'transaction_type' => 'sales',
                'status' => 'pending',
            ]);
            $transaction ? '' : abort(500, 'Something Went Wrong, We Are Working On It');

            $purchase = Purchase::create([
                'transactions_id' => $transaction->id,
                'user_id' => $this->order->user_id,
                'buyer_id' => Auth::id(),
                'address' => $order->address,
                'product_id' => $this->order->id,
                'delivery_status' => 'pending',
                'phone_no' => Auth::user()->phone_no,
            ]);
            $user = Auth::user()->firstname . ' ' . Auth::user()->lastname;
            $purchase ? '' : abort(500, 'Something Went Wrong, We Are Working On It');

            Notification::create([
                'user_id' => $this->order->user_id,
                'title' => $this->order->name . ' design has been ordered',
                'message' => "$user has purchase " . $this->order->name,
                'type' => 'sales',
            ]);

            session()->flash('status', true);

            $this->redirectIntended(route('market.place'), true);
        }
    }


}; ?>

<div class="h-screen" x-data="{
    modal: true
}">


    @if (session('product_added'))
        <div class="z-30 mt-10 toast toast-top toast-right"  id="status-message"  x-transition:leave="500ms" x-data="{ show: true }"
            x-show="show">


            <div class="font-extrabold text-white transition-all ease-out alert bg-navy-blue">
                <span>{{ session('item') }} added to cart </span>
                <span @click="show = false">
                    @svg('eva-close', 'h-6 w-6 text-red-500 cursor-pointer')
                </span>
            </div>

        </div>
    @endif
    @if (session('product_exists'))
        <div class="z-30 mt-10 toast toast-top toast-right" id="status-message" x-transition:leave="500ms" x-data="{ show: true }"
            x-show="show">
            <div class="font-extrabold text-white transition-all ease-out alert bg-navy-blue">
                <span>{{ session('item') }} is already in cart</span>
                <span @click="show = false">
                    @svg('eva-close', 'h-6 w-6 text-red-500 cursor-pointer')
                </span>
            </div>

        </div>
    @endif
    <div class="flex w-[100%] space-x-1">

        <x-market-place-sidebar class="" />
        <div class="relative bg-white w-screen md:w-[72%] lg:w-[80%] md:h-screen pb-5 md:pb-20">
            <div x-cloak="display:hidden"
                class="relative grid w-full h-full gap-5 px-5 pt-1 overflow-y-scroll lg:hidden md:grid-cols-2 sm:grid-cols-2">
                @foreach ($products as $product)
                    <x-product-card :$product />
                @endforeach
            </div>

            <div x-cloak="display:hidden"
                class="relative hidden w-full h-full gap-5 px-5 pt-1 overflow-y-scroll lg:grid md:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :$product />
                @endforeach

            </div>
            <div x-data="{ isOpen: @entangle('modal').live }" x-show="isOpen" x-cloak='display:none' x-transition.opacity
                class="fixed inset-0 z-20 w-screen h-full px-5 py-16 sm:py-24 md:py-44 md:px-20 lg:py-16 bg-black/40 backdrop-blur-sm pb-26">
                <div class="grid h-full bg-white lg:flex justify-evenly rounded-xl md:flex-row"
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

                    <form class="lg:w-[25%] px-6 md:px-10 lg:px-6 ">
                        <div class="flex flex-wrap mt-5 lg:mt-16">
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
                            <div class="flex-none w-full mt-2 text-sm font-medium text-black ">
                                @if ($order)
                                    In Stock
                                @endif
                            </div>
                        </div>
                        <div class="flex items-baseline mt-4 mb-3 text-gray-700 md:mb-6">
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

                            {{-- <a href="#" class="hidden ml-auto text-sm text-gray-500 underline md:block">Size
                                    Guide
                                </a> --}}
                        </div>
                        <x-input-error :messages="$errors->get('size')" class="mt-1" />
                        <div class="relative my-3 w-100">
                            <x-input-label class="font-extrabold bg-white " for="email" :value="__('Delivery Address')" />

                            <div class="w-full ">
                                <x-text-input wire:model="address" id="address"
                                    class="block w-full mt-2 text-black" type="text" name="email" required
                                    autocomplete="address" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>
                        </div>
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

</div>
