<?php

use Carbon\Carbon;
use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Purchase;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\AdminSetting;
use App\Rules\WithdrawalRule;
use App\Models\PlatformEarning;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, layout, uses, with, usesPagination};
usesPagination(view: 'vendor.livewire.tailwind', theme: 'simple');
layout('layouts.app');
uses(Toast::class);

state(['balance' => Auth::user()->wallet_balance]);
state(['dateSort' => 'desc']);
state('paginator');
state(['user' => Auth::user()->id]);
state(['count' => 10]);
state(['withdrawalModal' => false]);
state(['addFundModal' => false]);
state(['amount' => '']);
state(['withdrawal_commission' => fn() => AdminSetting::first()->value('commission_fee')]);
state(['currency' => fn() => AdminSetting::first()->value('currency_symbol')]);
state(['processing_time' => fn() => AdminSetting::first()->value('withdrawal_time')]);

with([
    'transactions' => fn() => Transaction::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate($this->count),
    'purchases' => fn() => Purchase::where('buyer_id', Auth::id())->orderBy('created_at', 'desc')->with('product')->paginate($this->count),
]);

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
    $withdrawal_amount = $validator['amount'];
    $withdrawal_charge = $validator['amount'] * $this->commission;

    $withdrawal = Withdrawal::create([
        'user_id' => Auth::id(),
        'amount' => $withdrawal_amount,
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
        'amount' => $withdrawal_amount,
        'charge' => $withdrawal_charge,
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

    $platform_earnings = PlatformEarning::create([
        'transaction_id' => $transaction->id,
        'quantity' => 1,
        'price' => $withdrawal_charge,
        'total' => $withdrawal_charge,
        'fee_type' => 'withdrawal_charge',
    ]);

    if (!$platform_earnings) {
        Withdrawal::where('id', '=', $withdrawal->id)->delete();
        Transaction::where('id', '=', $transaction->id)->delete();
        $user = User::where('id', '=', Auth::id())->first();
        $user->wallet_balance = $user->wallet_balance + ($validator['amount'] * $this->commission + $validator['amount']);
        $withdrawalDeducted = $user->save();

        $this->withdrawalModal = false;
        return $this->error('Withdrawal Error', 'An error has occured, but we are working on it');
    }

    $this->withdrawalModal = false;
    $this->success('Withdrawal Succesful', 'Your withdrawal request has been sent and will be processed within ' . $this->processing_time, timeout: 5000);
    $this->refresh();
};

?>
<div class="h-screen overflow-y-scroll bg-white pb-44 px-7 md:px-20 scrollbar-none" x-data="{
    transactions: true,
    purchases: false,
    setTab(tab) {
        if (tab === 'transactions') {
            this.transactions = true;
            this.purchases = false;
        } else if (tab === 'purchases') {
            this.transactions = false;
            this.purchases = true;
        }
    }
}">

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

    <div class="px-4 pb-3 text-black">


    </div>
    <div
        class="py-4 px-5 bg-gray-100 md:px-5 w-100 rounded-[14px] text-center md:items-center md:mt-5 lg:mt-0 md:justify-center mb-2 md:flex grid space-y-2 md:space-0">
        <div class="grid justify-start w-full md:w-1/2 ">
            <p class="grid justify-start pb-3 text-gray-400">Current Balance</p>
            <p class="grid justify-start text-4xl font-extrabold text-gray-700 duration-500 hover:scale-110">
                {{ AdminSetting::value('currency_symbol') . $balance }}
            </p>
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
    <div class="flex bg-gray-100 rounded-t-md">

        <h1 @click="setTab('transactions')" :class="transactions ? 'border-b-2 border-navy-blue' : ''"
            class="px-5 py-2 mt-5 text-2xl font-extrabold text-left text-gray-500 bg-gray-100 md:mt-3">
            Trasactions

        </h1>

        <h1 @click="setTab('purchases')" :class="purchases ? 'border-b-2 border-navy-blue' : ''"
            class="px-5 py-2 mt-5 text-2xl font-extrabold text-left text-gray-500 bg-gray-100 md:mt-3 ">
            Purchases

        </h1>
    </div>

    <div class="relative overflow-x-auto  shadow-md sm:rounded-b-[14px] h-72 bg-gray-100 scrollbar-none">

        <div x-show="transactions" x-transition:enter.duration.500ms x-cloak="display:none"
            class="w-full overflow-x-auto rounded-lg shadow-sm">
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
                                    {{ $currency }}{{ $transaction->amount }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $transaction->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $transaction->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $transaction->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                            ">
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



        {{-- Purchases --}}

        <div x-show="purchases" x-transition:enter.duration.500ms x-cloak="display:none"
            class="w-full overflow-x-auto rounded-lg shadow-sm scrollbar-none">
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
                                {{ $currency }}{{ $purchase->product->price }}
                                </th>
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                    {{ $currency }}{{ $purchase->material->price }}
                                </th>
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-center text-gray-900 capitalize whitespace-nowrap">
                                    {{ $purchase->quantity }}
                                </th>
                                <td
                                    class="px-4 py-3 text-center font-medium {{ $purchase->product->price > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $currency }}{{ $purchase->amount }}
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
    <div x-show="transactions" x-transition:enter.duration.500ms x-cloak="display:none">
        @if ($transactions != null || !$transactions->isEmpty())
                <div class="mt-4 sm:mt-6">
                    {{ $transactions->links() }}
                </div>
        @endif
    </div>
    {{-- Transaction Paginator End --}}



    {{-- Purchase Paginator --}}
    <div x-show="purchases" x-transition:enter.duration.500ms x-cloak="display:none">
        @if ($purchases != null || !$purchases->isEmpty())
                <div class="mt-4 sm:mt-6">
                    {{ $purchases->links() }}
                </div>
        @endif
    </div>
    {{-- Purchase Paginator End --}}

    <div x-transition:enter.duration.500ms x-cloak="display:none" x-cloak="display:hidden" wire:show='withdrawalModal'
        class="w-screen h-screen bg-black/30 backdrop-blur-sm inset-0 absolute z-[999]">
        <div class="flex justify-center md:px-20 lg:px-[30%] mt-[15%]">
            <x-bladewind.card class=" lg:w-full">

                <div class="flex flex-col justify-center">
                    <div class="grid ">
                        <div class="flex justify-between mb-5">
                             <img class="h-7 w-7" src="{{ asset('assets/twellr.png') }}" alt="twellr-logo" title="twellr-logo">
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
                    <p class="text-black w-[80%]"><b>Note:</b> <em class="text-sm">A withdrawal commission of {{ $withdrawal_commission }}% is applied to
                            each
                            withdrawal</em></p>

                </div>
            </x-bladewind.card>
        </div>
    </div>

    <div x-transition:enter.duration.500ms x-cloak="display:none" x-cloak="display:hidden" wire:show='addFundModal'
        class="w-screen h-screen bg-black/30 backdrop-blur-sm inset-0 absolute z-[999]">
        <div class="flex justify-center md:px-20 lg:px-[30%] mt-[15%]">
            <x-bladewind.card class=" lg:w-full">

                <div class="flex flex-col justify-center">
                    <div class="grid ">
                        <div class="flex justify-between mb-5">
                           <img class="h-7 w-7" src="{{ asset('assets/twellr.png') }}" alt="twellr-logo" title="twellr-logo">
                            <x-mary-button icon="o-x-mark"
                                class="left-0 justify btn-dark hover:bg-navy-blue btn-sm btn-circle"
                                wire:click='addFundModal = false' />
                        </div>
                        <x-input-label class="text-md">Amount</x-input-label>



                        <div class="grid justify-between space-y-2 lg:flex w-100">
                            <x-text-input name="amount" wire:model="amount" />
                            <x-mary-button label="Fund Now"
                                class="bg-[#001f54] text-white hover:bg-golden hover:border-golden h-12 rounded-xl"
                                wire:click="addFund" spinner />
                        </div>
                        <x-input-error :messages="$errors->get('amount')" />

                    </div>
                </div>
            </x-bladewind.card>
        </div>
    </div>

</div>
