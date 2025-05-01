<?php
use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Purchase;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\AdminSetting;
use function Livewire\Volt\{state, mount, layout, uses, with, usesPagination};
usesPagination(view: 'vendor.livewire.tailwind', theme: 'simple');
layout('layouts.admin');
uses(Toast::class);

state(['searchTerm' => '']);
state(['statusFilter' => '']);
state(['dateSort' => 'desc']);
state(['statuses' => ['pending', 'processing', 'completed', 'rejected']]);
state(['currentPage' => 1]);
state(['perPage' => 5]);
state('viewData');
state(['viewCard' => false]);

mount(function () {});

with([
    'withdrawals' => fn() => Withdrawal::orderBy('created_at', $this->dateSort)
        ->where(function ($query) {
            $query
                ->where('id', 'like', "%{$this->searchTerm}%")
                ->orWhereHas('user', function ($user) {
                    $user
                        ->where('firstname', 'like', "%{$this->searchTerm}%")
                        ->orWhere('lastname', 'like', "%{$this->searchTerm}%")
                        ->orWhere('email', 'like', "%{$this->searchTerm}%");
                });
        })
        ->where('status', 'like', "%$this->statusFilter%")
        ->with('user')
        ->paginate($this->perPage),
]);

// Method to update order status
$updateStatus = function ($withdrawal_id, $newStatus) {
    $withdrawal = Withdrawal::findOrFail($withdrawal_id);
    $transaction = Transaction::where('ref_no','=',$withdrawal->transaction_reference)->first();
    $oldStatus = $withdrawal->status;
    $withdrawal->status = $newStatus;

    $transaction->status = $newStatus;
    $withdrawal->save();
    $transaction->save();

    $this->success("Order #$withdrawal->id status changed from  $oldStatus to $newStatus");
    $this->viewData = Withdrawal::find($withdrawal->id);
};

// Method to reset filters

$resetFilters = function () {
    $this->searchTerm = '';
    $this->statusFilter = '';
};

// Method to toggle sort direction
$toggleSort = function () {
    $this->dateSort = $this->dateSort === 'desc' ? 'asc' : 'desc';

};

// Method to change page
$orderSearch = function () {
    //dd($this->searchTerm,$this->statusFilter);
    $query = Withdrawal::where('status', $this->statusFilter)->get();
    dd($this->orders);
};

$view = function ($id) {
    $this->viewData = Withdrawal::with('user')->find($id);

    $this->viewCard = true;
};
?>

<div class="fixed w-screen h-screen pt-1 overflow-hidden bg-gray-100">

    <div wire:loading class="toast toast-top top-28 z-[9999]">
        <div class="py-3 mb-6 text-white transition-opacity duration-500 bg-green-500 border border-green-500 rounded alert-info alert top-10"
            role="alert">
            <svg class="inline-block w-6 h-6 text-white animate-spin bw-spinner" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="ml-2">Loading...</span>
        </div>
    </div>

    <div wire:show='viewCard' x-cloak="display:none"
        class="fixed inset-0 w-screen h-full ease-in-out bg-black/40 backdrop-blur-sm z-[9999] px-10 py-20">
        <div class="bg-white ">
            <div class="flex justify-end bg-navy-blue">
                <span wire:click="viewCard=false" class="pr-5 my-2">
                    @svg('eva-close-circle', ['class' => 'w-7 h-7'])
                </span>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            # Order
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Customer
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Bank
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Account Name
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Account No
                        </th>
                            <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Price
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Date
                        </th>


                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if ($viewData)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                #{{ $viewData->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ "{$viewData->user->firstname} {$viewData->user->lastname} " ?? 'N/A' }}<br>
                                <span class="text-xs">{{ $viewData->user->email ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $viewData->bank_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 capitalize grid">
                                <span>{{ $viewData->account_name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $viewData->account_no}}
                            </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ AdminSetting::value('currency_symbol').number_format($viewData->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                {{ $viewData->created_at->format('M d, Y H') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $viewData->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $viewData->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $viewData->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $viewData->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($viewData->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <select x-on:change="$wire.updateStatus({{ $viewData->id }}, event.target.value )"
                                        class="text-xs text-black border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}"
                                                {{ $viewData->status === $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </td>
                        </tr>

                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="fixed flex w-screen h-screen gap-1 overflow-hidden bg-gray-100">
        <!-- Sidebar component -->
        <x-admin-sidebar />
        <!-- Main content -->
        <div class="w-full px-1 pb-2 mb-16 overflow-y-scroll bg-white">
            <header class="flex items-center justify-between w-full px-5 mb-1 bg-white">
                <h2 class="py-4 text-3xl font-extrabold text-gray-500 capitalize">
                    {{ __('Orders') }}
                </h2>
            </header>

            <!-- Filters and search -->
            <div class="px-5 py-3 mb-4 bg-white rounded-lg shadow">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <!-- Search -->
                    <div class="w-full md:w-1/3">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input wire:model.live="searchTerm" @keypress="$wire.searchTerm = event.target.value"
                                id="search" type="text" placeholder="Search by order #, customer or product"
                                class="block w-full py-2 pl-10 pr-3 text-gray-500 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Status filter -->
                    <div class="w-full md:w-1/4">
                        <label for="status" class="sr-only">Status</label>
                        <select wire:model.live="statusFilter" id="status"
                            @change="$wire.statusFilter = event.target.value"
                            class="block w-full px-3 py-2 text-gray-500 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:navy-blue sm:text-sm">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort control -->
                    <div class="flex justify-end w-full md:w-1/4">
                        <button wire:click="toggleSort"
                            class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span>Date: {{ ucfirst($dateSort) }}</span>
                            <svg class="w-5 h-5 ml-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </button>
                        <button wire:click="resetFilters"
                            class="px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
            <div class="px-4 pb-3">
                {{ $withdrawals->links() }}
            </div>
            <!-- Orders table -->
            <div class="w-full  overflow-x-scroll">
                <div class=" border-b border-gray-200 shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-1 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    # Order
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Customer
                                </th>
                                  <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Bank
                                </th>
                                  <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Account Name
                                </th>
                                  <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Account Number
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Amount
                                </th>
                                     <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Date
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($withdrawals as $withdrawal)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                        #{{ $withdrawal->id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ "{$withdrawal->user->firstname} {$withdrawal->user->lastname} " ?? 'N/A' }}<br>
                                        <span class="text-xs">{{ $withdrawal->user->email ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $withdrawal->bank_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $withdrawal->account_name }}
                                    </td>
                                      <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $withdrawal->account_no }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $withdrawal->amount }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $withdrawal->created_at->format('M d, Y H') }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $withdrawal->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $withdrawal->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $withdrawal->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $withdrawal->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($withdrawal->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">
                                            <select
                                                x-on:change="$wire.updateStatus({{ $withdrawal->id }}, event.target.value )"
                                                class="text-xs text-black border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                @foreach ($statuses as $status)
                                                    <option value="{{ $status }}"
                                                        {{ $withdrawal->status === $status ? 'selected' : '' }}>
                                                        {{ ucfirst($status) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <a wire:click='view({{ $withdrawal->id }})'
                                                class="text-indigo-600 cursor-pointer hover:text-indigo-900 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                        No Withdrawal Request found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>
</div>
