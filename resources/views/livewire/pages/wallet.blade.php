<?php
use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\Purchase;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public $balance;
    public $transactions;
    public $purchases;
    public $paginator;
    public int $user;
    public int $count = 10;

    public function mount()
    {
        $this->user = Auth::user()->id;
        $this->balance = '$' . Auth::user()->wallet_balance;
        switch (Auth::user()->role) {
            case 'user':
                $this->purchases = Purchase::where('buyer_id', '=', Auth::id())->with('product')->paginate($this->count)->reverse();
                break;
            case 'creative':
                $this->transactions = Transaction::where('user_id', '=', Auth::id())->paginate($this->count)->reverse();
                break;
        }
    }
    public function addFunds()
    {
        redirect(route('add.funds'))->withInput([
            'user_id' => Auth::user()->id,
            'balance' => Auth::user()->wallet_balance,
        ]);
    }

    public function withdraw() {}
};
?>
<div class="pb-5 bg-white px-7 md:px-20" x-data="{
    current: true
}">
    <header class="">
        <h2 class="pt-2 text-3xl font-extrabold text-gray-500">
            {{ __('Wallet') }}
        </h2>
    </header>
    <div
        class="py-4 px-5 bg-gray-100 md:px-5 w-100 rounded-[14px] text-center md:items-center md:mt-5 lg:mt-0 md:justify-center mb-2 md:flex grid space-y-2 md:space-0">
        <div class="grid justify-start w-full md:w-1/2 ">
            <p class="grid justify-start pb-3 text-gray-400">Current Balance</p>
            <p class="grid justify-start text-4xl font-extrabold text-gray-700 duration-500 hover:scale-110">
                {{ $balance }}</p>
        </div>
        <div class="flex justify-center gap-5 font-bold md:justify-end md:w-1/2">
            <button wire:click="addFunds"
                class="px-5 py-2 duration-500 bg-golden text-neutral-600 hover:scale-110 rounded-xl">Add
                Funds</button>
            @if (Auth::user()->isCreative())
                <button
                    class="bg-white border-[1px] border-neutral-600 text-black py-2 px-5 rounded-xl hover:scale-110 duration-500 ">Withdraw</button>
            @endif
        </div>
    </div>
    <h1 class="px-5 py-2 mt-5 md:mt-3 text-2xl font-extrabold text-left  text-gray-500 bg-gray-100 rounded-t-[14px]">
        Trasaction History

    </h1>

    <div class="relative overflow-x-auto  shadow-md sm:rounded-b-[14px] h-72 bg-gray-100">
        @if (Auth::user()->isCreative())


            <div class="w-full overflow-x-auto rounded-lg shadow-sm">
                @if ($transactions == null || $transactions->isEmpty())
                    <div class="flex items-center justify-center p-8 bg-white">
                        <div class="text-center">
                            <p class="font-medium text-gray-500">No Transactions Found</p>
                            <p class="mt-1 text-sm text-gray-400">Your transaction history will appear here</p>
                        </div>
                    </div>
                @else
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="sticky top-0 text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Transaction
                                </th>

                                <th scope="col" class="px-4 py-3 text-center">
                                    Amount
                                </th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Status
                                </th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($transactions as $transaction)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <th scope="row"
                                        class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                        {{ $transaction->transaction_type }}
                                    </th>
                                    <td
                                        class="px-4 py-3 text-center font-medium {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->amount }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                @if ($transaction->status == 'completed') bg-green-100 text-green-800
                                @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($transaction->status == 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                            {{ $transaction->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500">
                                        {{ Carbon::parse($transaction->created_at)->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Responsive version for small screens (hidden on md and above) -->
            <div class="mt-4 md:hidden">
                @if ($transactions == null || $transactions->isEmpty())
                    <div class="p-6 text-center bg-white rounded-lg">
                        <p class="text-gray-500">No transactions found</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($transactions as $transaction)
                            <div class="p-4 bg-white rounded-lg shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <span
                                        class="font-medium capitalize">{{ $transaction->transaction_type }}hghgh</span>
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                            @if ($transaction->status == 'completed') bg-green-100 text-green-800
                            @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($transaction->status == 'failed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                                        {{ $transaction->status }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="font-medium {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->amount }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ Carbon::parse($transaction->created_at)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if (!Auth::user()->isCreative())

            <div class="w-full overflow-x-auto rounded-lg shadow-sm">
                @if ($purchases == null || $purchases->isEmpty())
                    <div class="flex items-center justify-center p-8 bg-white">
                        <div class="text-center">
                            <p class="font-medium text-gray-500">No Purchases Found</p>
                            <p class="mt-1 text-sm text-gray-400">Your purchase history will appear here</p>
                        </div>
                    </div>
                @else
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="sticky top-0 text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Transaction
                                </th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Item
                                </th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Amount
                                </th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Delivery Status
                                </th>
                                <th scope="col" class="px-4 py-3 text-center">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($purchases as $purchase)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <th scope="row"
                                        class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                        purchase
                                    </th>
                                    <th scope="row"
                                        class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                        {{ $purchase->product->name }}
                                    </th>
                                    <td
                                        class="px-4 py-3 text-center font-medium {{ $purchase->product->price > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $purchase->product->price }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                @if ($purchase->delivery_status == 'delivered') bg-green-100 text-green-800
                                @elseif($purchase->delivery_status == 'shipping') bg-blue-100 text-blue-800
                                @elseif($purchase->delivery_status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($purchase->delivery_status == 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                            {{ $purchase->delivery_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-500">
                                        {{ Carbon::parse($purchase->created_at)->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Responsive version for small screens (hidden on md and above) -->
            <div class="mt-4 md:hidden">
                @if ($purchases == null || $purchases->isEmpty())
                    <div class="p-6 text-center bg-white rounded-lg">
                        <p class="text-gray-500">No purchases found</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($purchases as $purchase)
                            <div class="p-4 bg-white rounded-lg shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium capitalize">{{ $purchase->transaction_type }}</span>
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                            @if ($purchase->status == 'delivered') bg-green-100 text-green-800
                            @elseif($purchase->status == 'shipping') bg-blue-100 text-blue-800
                            @elseif($purchase->status == 'processing') bg-yellow-100 text-yellow-800
                            @elseif($purchase->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                                        {{ $purchase->status }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="font-medium {{ $purchase->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $purchase->amount }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ Carbon::parse($purchase->created_at)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
    <div class="flex justify-between py-2 bg-white">

@php
if(Auth::user()->isCreative()) {
         $paginator = $transaction->paginate($count) ;
} else {
             $paginator = $purchase->paginate($count);
}
@endphp





        <div class="justify-start text-sm text-black">Showing {{ $paginator->firstItem() ?? 0 }} to
            {{ $paginator->lastItem() ?? 0 }} of
            {{ $paginator->total() }} results
        </div>

        <div class="flex flex-wrap items-center justify-end gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button disabled
                    class="px-2 py-1 text-xs font-medium text-gray-400 bg-white border border-gray-300 rounded-md cursor-not-allowed">
                    <span class="hidden sm:inline">Previous</span>
                    <span class="sm:hidden">&larr;</span>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:navigate>
                    <button
                        class="px-2 py-1 text-xs font-medium text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-golden hover:text-white focus:outline-none focus:ring ring-blue-300 focus:border-blue-300 active:bg-gray-100">
                        <span class="hidden sm:inline">Previous</span>
                        <span class="sm:hidden">&larr;</span>
                    </button>
                </a>
            @endif

            <div class="items-center hidden gap-1 sm:flex">
                {{-- Pagination Elements - Desktop --}}
                @php
                    $start = max(1, $paginator->currentPage() - 1);
                    $end = min($start + 2, $paginator->lastPage());
                    if ($end - $start < 2 && $start > 1) {
                        $start = max(1, $end - 2);
                    }
                @endphp

                @if ($start > 1)
                    <a href="{{ $paginator->url(1) }}" wire:navigate>
                        <button
                            class="px-2 py-1 text-xs font-medium text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-golden hover:text-white focus:outline-none focus:ring ring-blue-300 focus:border-blue-300 active:bg-gray-100">
                            1
                        </button>
                    </a>
                    @if ($start > 2)
                        <span class="px-1 py-1 text-xs text-gray-500">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    @if ($paginator->currentPage() == $i)
                        <button aria-current="page" disabled
                            class="relative px-2 py-1 text-xs font-bold text-white border rounded-md bg-golden border-golden">
                            {{ $i }}
                            <span
                                class="absolute w-1 h-1 transform -translate-x-1/2 rounded-full -bottom-1 left-1/2 bg-golden"></span>
                        </button>
                    @else
                        <a href="{{ $paginator->url($i) }}" wire:navigate>
                            <button
                                class="px-2 py-1 text-xs font-medium text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-golden hover:text-white focus:outline-none focus:ring ring-blue-300 focus:border-blue-300 active:bg-gray-100">
                                {{ $i }}
                            </button>
                        </a>
                    @endif
                @endfor

                @if ($end < $paginator->lastPage())
                    @if ($end < $paginator->lastPage() - 1)
                        <span class="px-1 py-1 text-xs text-gray-500">...</span>
                    @endif
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" wire:navigate>
                        <button
                            class="px-2 py-1 text-xs font-medium text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-golden hover:text-white focus:outline-none focus:ring ring-blue-300 focus:border-blue-300 active:bg-gray-100">
                            {{ $paginator->lastPage() }}
                        </button>
                    </a>
                @endif
            </div>

            {{-- Mobile Page Indicator --}}
            <span class="px-2 py-1 text-xs font-medium sm:hidden">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:navigate>
                    <button
                        class="px-2 py-1 text-xs font-medium text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-golden hover:text-white focus:outline-none focus:ring ring-blue-300 focus:border-blue-300 active:bg-gray-100">
                        <span class="hidden sm:inline">Next</span>
                        <span class="sm:hidden">&rarr;</span>
                    </button>
                </a>
            @else
                <button disabled
                    class="px-2 py-1 text-xs font-medium text-gray-400 bg-white border border-gray-300 rounded-md cursor-not-allowed">
                    <span class="hidden sm:inline">Next</span>
                    <span class="sm:hidden">&rarr;</span>
                </button>
            @endif
        </div>
    </div>

</div>
