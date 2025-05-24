@if (!empty(Auth::user()->avatar) && str_contains(Auth::user()->avatar, 'https://'))
    <x-bladewind.avatar class="ring-0 border-0 aspect-square" size="medium" :image="Auth::user()->avatar" />
@else
    @if (Auth::user()->avatar)
        <x-bladewind.avatar class="ring-0  border-0 " size="medium"
            image="{{ asset('uploads/avatar/' . Auth::user()->avatar) }}" />
    @else
        <x-bladewind.avatar class="ring-0 border-0" size="medium" image="{{ asset('assets/icons-user.png') }}" />
    @endif
@endif
