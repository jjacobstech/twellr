<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public int $ratings = 5;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->firstname = Auth::user()->firstname;
        $this->lastname = Auth::user()->lastname;
        $this->email = Auth::user()->email;
    }
}; ?>

<div class="w-100">
    <header class="">
        <h2 class="text-2xl font-extrabold text-gray-500 py-4">
            {{ __('Profile') }}
        </h2>
    </header>
    <div class="w-full ">
        <div
            style="background-image: url('{{ asset('assets/pexels-solliefoto-298863.jpg') }}')"class="border-[#bebebe] border-[1px] rounded-xl bg-no-repeat bg-cover justify-center items-center w-full">

            <div class="relative w-full flex justify-center">
                <img class="w-36 h-36 rounded-full absolute  z-50 mt-20 border-[#bebebe] border-[1px]"
                    src="{{ asset('assets/pexels-solliefoto-298863.jpg') }}" alt="{{ Auth::user()->name }}" />
            </div>
            <div class=" py-10 w-100 bg-white rounded-xl mt-40 text-center items-center justify-center grid">
                <h1 class="text-2xl font-bold text-gray-800 my-5">{{ Auth::user()->firstname }}
                    {{ Auth::user()->lastname }}</h1>
                <span class="flex items-center">
                    @for ($rating = 0; $rating < $ratings; $rating++)
                        <x-star />
                    @endfor
                </span>
                <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>

</div>
