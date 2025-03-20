@if (auth()->user()->avatar)
    {{-- <img class="w-10 h-10 rounded-full bg-white border" src="{{ asset('uploads/avatar/' . auth()->user()->avatar) }}"
        title="{{ auth()->user()->name }}"> --}}
    <x-bladewind.avatar class="ring-0  border-0 " size="medium"
        image="{{ asset('uploads/avatar/' . auth()->user()->avatar) }}" />
@else
    {{-- <img class="w-10 h-10 rounded-full bg-white border" src="{{ asset('assets/icons-user.png') }}"
        title="{{ auth()->user()->name }}"> --}}
    <x-bladewind.avatar class="ring-0 border-0" size="medium" image="{{ asset('assets/icons-user.png') }}" />
@endif
