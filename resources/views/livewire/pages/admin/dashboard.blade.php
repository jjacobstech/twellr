<?php

use Mary\Traits\Toast;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Notification;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\layout;
Layout('layouts.admin');

?>
<div class="flex mx-auto md:w-full lg:px gap-1 mt-1">
    <x-admin-sidebar />

    <div class="w-full px-2 bg-white">
        <div class="flex justify-evenly">
            <div class="flex w-[63%] gap-5">
                <div class="p-10 w-[45%] bg-pink-500 border m-5 rounded-xl">gg</div>
                <div class="p-10 w-[45%] bg-pink-500 border m-5 ml-2 rounded-xl">gx</div>
            </div>
            <div class=" mx-[2.5%] w-[30%]">
                <livewire:pages.admin.charts />
            </div>
        </div>
        <div class="flex ">
            <div class="w-[60%] p-10 bg-pink-500 border m-6 rounded-xl">hh</div>
            <div class="p-10 w-[30%] bg-pink-500 border m-6 ml-7 rounded-xl">gg</div>
        </div>
        <div class="flex ">
            <div class="w-[100%] p-10 rounded-lg bg-pink-500 border m-7">hh</div>
        </div>
        <p class="w-full text-center">Copyright © 2025 Twellr. All Rights Reserved </p>
    </div>
</div>
