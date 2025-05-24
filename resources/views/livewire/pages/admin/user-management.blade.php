<?php
use App\Models\User;
use Mary\Traits\Toast;
use App\Models\AdminSetting;
use App\Models\BlogCategory;
use function Livewire\Volt\{state, mount, layout, uses, with, usesPagination};

usesPagination(view: 'vendor.livewire.tailwind', theme: 'simple');
layout('layouts.admin');
uses(Toast::class);

state(['searchTerm' => '']);
state(['roleFilter' => '']);
state(['dateSort' => 'asc']);
state(['currentPage' => 1]);
state(['perPage' => 5]);
state(['user' => null]);
state(['usersList' => true]);
state(['viewUser' => null]);
state(['viewCard' => false]);
state([
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'avatar' => '',
    'cover' => '',
    'address' => '',
    'phone_no' => '',
    'bank_name' => '',
    'account_no' => '',
    'account_name' => '',
    'x' => '',
    'facebook' => '',
    'whatsapp' => '',
    'instagram' => '',
    'wallet_balance' => '',
]);

with([
    'users' => fn() => ($users = User::query()
        ->when($this->searchTerm, function ($query) {
            $query->where(function ($query) {
                $query
                    ->where('firstname', 'like', "%{$this->searchTerm}%")
                    ->orWhere('lastname', 'like', "%{$this->searchTerm}%")
                    ->orWhere('email', 'like', "%{$this->searchTerm}%");
            });
        })
        ->when($this->roleFilter, function ($query) {
            $query->where('role', 'like', "%{$this->roleFilter}%");
        })
        ->orderBy('created_at', $this->dateSort)
        ->where('role','!=','admin')
        ->paginate($this->perPage)),
]);

// Method to reset filters

$resetFilters = function () {
    $this->searchTerm = '';
    $this->roleFilter = '';
};

// Method to toggle sort direction
$toggleSort = function () {
    $this->dateSort = $this->dateSort === 'desc' ? 'asc' : 'desc';
};

$toggleCategory = function ($id) {
    return $this->roleFilter = $id;
};

$delete = function ($id) {
    $user = User::find($id);
    if (!$user) {
        $this->error('User Deletion Error', 'Something went Wrong');
    } else {
        $user->delete();
        $this->success('User Deleted Successfully');
    }
};
$editBalance = function ($id) {
    $user = User::find($id);
    $this->user = $user;
    $this->viewCard = true;
    $this->wallet_balance = $user->wallet_balance;
};
$close = function () {
    $this->viewCard = false;
    $this->viewData = null;
    $this->wallet_balance = '';
    $this->usersList = true;
};

$save = function () {
    $userData = (object) $this->validate(['wallet_balance' => 'required|numeric']);
    $this->user->wallet_balance = $userData->wallet_balance;
    $this->user->save();
    $this->viewCard = false;
    $this->wallet_balance = '';
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

    <div class="fixed flex w-screen h-screen gap-1 overflow-hidden bg-gray-100">
        <!-- Sidebar component -->
        <x-admin-sidebar />
        <!-- Main content -->
        <div x-cloak="display:none" class="w-full px-1 pb-2 mb-16 overflow-y-scroll bg-white scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
            <header class="flex items-center justify-between w-full px-5 mb-1 bg-white">
                <h2 class="py-4 text-3xl font-extrabold text-gray-500 capitalize">
                    {{ __('User Management') }}
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
                                id="search" type="text" placeholder="Search by Name/Email"
                                class="block w-full py-2 pl-10 pr-3 text-gray-500 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Category filter -->
                    <div class="w-full md:w-1/4">
                        <label for="status" class="sr-only">Status</label>
                        <select wire:model.live="roleFilter" id="status"
                            @change="$wire.roleFilter = event.target.value"
                            class="block w-full px-3 py-2 text-gray-500 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:navy-blue sm:text-sm">
                            <option value="">Role</option>
                            <option value="user">User</option>
                            <option value="creative">Creative</option>

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
            <div class="px-4">
                {{ $users->links() }}
            </div>

            <div class="w-full px-5 l  scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
                <div
                    class="overflow-x-scroll border-b border-gray-200 shadow sm:rounded-lg scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
                    <table
                        class="min-w-full divide-y  overflow-x-scroll divide-gray-200 scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    # User ID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Name<br>Email
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Avatar
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase">
                                    Referral Link
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Wallet Balance
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Role
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Signup Date
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 ">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                        #{{ $user->id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ "{$user->firstname} {$user->lastname} " ?? 'N/A' }}<br>
                                        <span class="text-xs">{{ $user->email ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        @if (!empty($user->avatar) && str_contains($user->avatar, 'https://'))
                                        <x-bladewind.avatar class="ring-0 border-0 aspect-square" size="medium" :image="url($user->avatar) " />
                                    @else
                                        @if (auth()->user()->avatar)
                                            <x-bladewind.avatar class="ring-0  border-0 " size="medium"
                                                image="{{ asset('uploads/avatar/' . auth()->user()->avatar) }}" />
                                        @else
                                            <x-bladewind.avatar class="ring-0 border-0" size="medium" image="{{ asset('assets/icons-user.png') }}" />
                                        @endif
                                    @endif

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $user->referral_link ?? 'N/A' }}
                                    </td>


                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ number_format($user->wallet_balance, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $user->role === 'creative' ? 'bg-navy-blue text-white' : '' }}
                                             {{ $user->role === 'user' ? 'bg-golden text-white' : '' }}
                                            {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                          ">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $user->created_at->format('M d, Y H') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">

                                            </select>
                                            <a wire:click='editBalance({{ $user->id }})'
                                                class="text-indigo-600 cursor-pointer hover:text-indigo-900 ">
                                                Edit Balance
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-4 text-sm text-center text-gray-500 whitespace-nowrap">
                                        No User found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


            </div>
        </div>


    <div x-transition:enter.duration.500ms x-cloak="display:none" x-cloak="display:hidden" wire:show='viewCard'
        class="w-screen h-screen bg-black/30 backdrop-blur-sm inset-0 absolute z-[999]">
        <div class="flex justify-center md:px-20 lg:px-[30%] mt-[15%]">
            <x-bladewind.card class=" lg:w-full">

                <div class="flex flex-col justify-center">
                    <div class="grid ">
                        <div class="flex justify-end">
                            <x-mary-button icon="o-x-mark"
                                class="left-0 justify btn-dark hover:bg-navy-blue btn-sm btn-circle"
                                wire:click='close' />
                        </div>
                        <x-input-label class="text-md">Balance</x-input-label>



                        <div class="grid justify-between space-y-2 lg:flex w-100">
                            <x-text-input name="wallet_balance" wire:model="wallet_balance" />
                            <x-mary-button label="Save"
                                class="bg-[#001f54] text-white hover:bg-golden hover:border-golden h-12"
                                wire:click="save" spinner />
                        </div>
                        <x-input-error :messages="$errors->get('amount')" />

                    </div>
                </div>
            </x-bladewind.card>
        </div>
    </div>

    </div>
</div>
