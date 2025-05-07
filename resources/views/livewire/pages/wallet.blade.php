<?php

use Carbon\Carbon;
use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Purchase;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\AdminSetting;
use App\Rules\WithdrawalRule;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, layout, uses, usesPagination};
usesPagination(view: 'vendor.livewire.tailwind', theme: 'simple');
layout('layouts.app');
uses(Toast::class);

state(['balance' => Auth::user()->wallet_balance]);
state(['dateSort' => 'desc']);
state('paginator');
state(['user' => Auth::user()->id]);
state(['count' => 10]);
state(['transactions' => fn() => Transaction::where('user_id', '=', Auth::id())->paginate($this->count)->reverse()]);
state(['purchases' => fn() => Purchase::where('buyer_id', '=', Auth::id())->with('product')->paginate($this->count)->reverse()]);
state(['withdrawalModal' => false]);
state(['addFundModal' => false]);
state('amount');
state(['commission' => fn() => AdminSetting::first()->value('commission_fee')]);
state(['processing_time' => fn() => AdminSetting::first()->value('withdrawal_time')]);

$addFund = function () {
    $validator = $this->validate(
        [
            'amount' => ['required', 'numeric'],
        ],
        [
            'amount.required' => 'Add the amount you want to fund',
            'amount.numeric' => 'Wrong Amount',
        ],
    );

    $this->redirect(route('fund.wallet', ['amount' => $validator['amount']]));
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

$withdraw = function () {
    $validator = $this->validate(
        [
            'amount' => ['required', 'numeric', new WithdrawalRule()],
        ],
        [
            'amount.required' => 'Add the amount you want to withdraw',
            'amount.numeric' => 'Wrong Amount',
        ],
    );

    $ref_no = $this->generateReferenceNumber();

    $withdrawal = Withdrawal::create([
        'user_id' => Auth::id(),
        'amount' => $validator['amount'] * $this->commission + $validator['amount'],
        'account_name' => Auth::user()->account_name,
        'account_no' => Auth::user()->account_no,
        'bank_name' => Auth::user()->bank_name,
        'status' => 'pending',
        'transaction_reference' => $ref_no,
    ]);

    if (!$withdrawal) {
        $this->withdrawalModal = false;
        return $this->error('Withdrawal Error', 'An error has occured, but we are working on it');
    }

    $transaction = Transaction::create([
        'user_id' => Auth::id(),
        'buyer_id' => Auth::id(),
        'amount' => $validator['amount'] * $this->commission + $validator['amount'],
        'transaction_type' => 'withdrawal',
        'status' => 'pending',
        'ref_no' => $ref_no,
    ]);

    if (!$transaction) {
        Withdrawal::where('id', '=', $withdrawal->id)->delete();
        $this->withdrawalModal = false;
        return $this->error('Withdrawal Error', 'An error has occured, but we are working on it');
    }
    $user = User::where('id', '=', Auth::id())->first();
    $user->wallet_balance = $user->wallet_balance - ($validator['amount'] * $this->commission + $validator['amount']);
    $withdrawalDeducted = $user->save();

    if (!$withdrawalDeducted) {
        Withdrawal::where('id', '=', $withdrawal->id)->delete();
        Transaction::where('id', '=', $transaction->id)->delete();

        $this->withdrawalModal = false;
        return $this->error('Withdrawal Error', 'An error has occured, but we are working on it');
    }

    $this->withdrawalModal = false;
    $this->success('Withdrawal Succesful', 'Your withdrawal request has been sent and will be processed within ' . $this->processing_time, timeout: 5000);
};

?>
<div class="pb-5 bg-white px-7 md:px-20" x-data="{transactions: true, purchases: false}">

    @session('error')
        {{ $this->error(session('error'), timeout: 5000) }}
    @endsession

    @session('success')
        {{ $this->success(session('success'), timeout: 5000) }}
    @endsession

    <header class="">
        <h2 class="pt-2 text-3xl font-extrabold text-gray-500">
            {{ __('Wallet') }}
        </h2>
    </header>

    <div class="px-4 pb-3">

    </div>
    <div
        class="py-4 px-5 bg-gray-100 md:px-5 w-100 rounded-[14px] text-center md:items-center md:mt-5 lg:mt-0 md:justify-center mb-2 md:flex grid space-y-2 md:space-0">
        <div class="grid justify-start w-full md:w-1/2 ">
            <p class="grid justify-start pb-3 text-gray-400">Current Balance</p>
            <p class="grid justify-start text-4xl font-extrabold text-gray-700 duration-500 hover:scale-110">
                {{ AdminSetting::value('currency_symbol') . $balance }}</p>
        </div>
        <div class="flex justify-center gap-5 font-bold md:justify-end md:w-1/2">
            <button wire:click="addFundModal = true"
                class="px-5 py-2 duration-500 bg-golden text-neutral-600 hover:scale-110 rounded-xl">Add
                Funds</button>
            @if (Auth::user()->isCreative())
                <button wire:click='withdrawalModal = true'
                    class="bg-white border-[1px] border-neutral-600 text-black py-2 px-5 rounded-xl hover:scale-110 duration-500 ">Withdraw</button>
            @endif
        </div>
    </div>
<div class="flex">

    <h1  :class="transactions ? 'border-b border-navy-blue' : '' " class="px-5 py-2 mt-5 md:mt-3 text-2xl font-extrabold text-left  text-gray-500 bg-gray-100 rounded-t-[14px]">
        Trasactions

    </h1>

     <h1  :class="purchases ? 'border-b border-navy-blue' : '' " class="px-5 py-2 mt-5 md:mt-3 text-2xl font-extrabold text-left  text-gray-500 bg-gray-100 rounded-t-[14px]">
        Purchases

    </h1>
</div>

    <div class="relative overflow-x-auto  shadow-md sm:rounded-b-[14px] h-72 bg-gray-100 scrollbar-none">

        <div x-show="transactions" x-transition:enter.duration.500ms x-cloak="display:none"  class="w-full overflow-x-auto rounded-lg shadow-sm">
            @if ($transactions == null || $transactions->isEmpty())
                <div class="flex items-center justify-center p-8 bg-white">
                    <div class="text-center">
                        <p class="font-medium text-gray-500">No Transactions Found</p>
                        <p class="mt-1 text-sm text-gray-400">Your transaction history will appear here</p>
                    </div>
                </div>
            @else
                <table class="w-full text-sm text-left text-gray-500 scrollbar-none">
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
                                    ${{ $transaction->amount }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $transaction->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $transaction->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($transaction->status) }}
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

        <div x-show="purchases" x-transition:enter.duration.500ms x-cloak="display:none" class="w-full overflow-x-auto rounded-lg shadow-sm scrollbar-none">
            @if ($purchases == null || $purchases->isEmpty())
                <div class="flex items-center justify-center p-8 bg-white">
                    <div class="text-center">
                        <p class="font-medium text-gray-500">No Purchases Found</p>
                        <p class="mt-1 text-sm text-gray-400">Your purchase history will appear here</p>
                    </div>
                </div>
            @else
                <table class="w-full text-sm text-left text-gray-500 scrollbar-none">
                    <thead class="sticky top-0 text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-center">
                                Transaction
                            </th>
                            <th scope="col" class="px-4 py-3 text-center">
                                Item
                            </th>
                            <th scope="col" class="px-4 py-3 text-center">
                                Price
                            </th>
                            <th scope="col" class="px-4 py-3 text-center">
                                Material
                            </th>
                            <th scope="col" class="px-4 py-3 text-center">
                                Quantity
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
                    <tbody class="bg-white divide-y divide-gray-200 scrollbar-none">
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
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                    {{ $purchase->product->price }}
                                </th>
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                    {{ $purchase->material->price }}
                                </th>
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                    {{ $purchase->quantity }}
                                </th>
                                <td
                                    class="px-4 py-3 text-center font-medium {{ $purchase->product->price > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ $purchase->amount }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $purchase->delivery_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $purchase->delivery_status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $purchase->delivery_status === 'shipped' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                            {{ $purchase->delivery_status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $purchase->delivery_status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($purchase->delivery_status) }}
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

    </div>

    {{-- Tranaction Paginator --}}
    <div x-show="transactions" x-transition:enter.duration.500ms x-cloak="display:none"  class="flex justify-between py-2 bg-white">

        @php
            $paginator = Transaction::where('user_id', '=', Auth::id())->paginate($count);
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
    {{-- Transaction Paginator End --}}


    {{-- Purchase Paginator --}}
    <div x-show="purchases" x-transition:enter.duration.500ms x-cloak="display:none"  class="flex justify-between py-2 bg-white">

        @php
            $paginator = Purchase::where('buyer_id', '=', Auth::id())->with('product')->paginate($count);
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
    {{-- Purchase Paginator End --}}

    <div x-transition:enter.duration.500ms x-cloak="display:none"  x-cloak="display:hidden" wire:show='withdrawalModal'
        class="w-screen h-screen bg-black/30 backdrop-blur-sm inset-0 absolute z-[999]">
        <div class="flex justify-center md:px-20 lg:px-[30%] mt-[15%]">
            <x-bladewind.card class=" lg:w-full">

                <div class="flex flex-col justify-center">
                    <div class="grid ">
                        <div class="flex justify-end">
                            <x-mary-button icon="o-x-mark"
                                class="left-0 justify btn-dark hover:bg-navy-blue btn-sm btn-circle"
                                wire:click='withdrawalModal = false' />
                        </div>
                        <x-input-label class="text-md">Amount</x-input-label>



                        <div class="grid justify-between space-y-2 lg:flex w-100">
                            <x-text-input wire:model="amount" />
                            <x-mary-button label="Withdraw"
                                class="bg-[#001f54] text-white hover:bg-golden hover:border-golden h-12"
                                wire:click="withdraw" spinner />
                        </div>
                        <x-input-error :messages="$errors->get('amount')" />

                    </div>
                    <p class="text-black w-[80%]"><b>Note:</b> <em class="text-sm">A withdrawal charge is applied to
                            all
                            withdrawal</em></p>

                </div>
            </x-bladewind.card>
        </div>
    </div>

    <div x-transition:enter.duration.500ms x-cloak="display:none"  x-cloak="display:hidden" wire:show='addFundModal'
        class="w-screen h-screen bg-black/30 backdrop-blur-sm inset-0 absolute z-[999]">
        <div class="flex justify-center md:px-20 lg:px-[30%] mt-[15%]">
            <x-bladewind.card class=" lg:w-full">

                <div class="flex flex-col justify-center">
                    <div class="grid ">
                        <div class="flex justify-end">
                            <x-mary-button icon="o-x-mark"
                                class="left-0 justify btn-dark hover:bg-navy-blue btn-sm btn-circle"
                                wire:click='addFundModal = false' />
                        </div>
                        <x-input-label class="text-md">Amount</x-input-label>



                        <div class="grid justify-between space-y-2 lg:flex w-100">
                            <x-text-input name="amount" wire:model="amount" />
                            <x-mary-button label="Fund"
                                class="bg-[#001f54] text-white hover:bg-golden hover:border-golden h-12"
                                wire:click="addFund" spinner />
                        </div>
                        <x-input-error :messages="$errors->get('amount')" />

                    </div>
                </div>
            </x-bladewind.card>
        </div>
    </div>

</div>
