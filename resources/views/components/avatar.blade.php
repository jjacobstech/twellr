@if (auth()->user()->avatar)
    <x-bladewind.avatar class="ring-0  border-0 " size="medium"
        image="{{ asset('uploads/avatar/' . auth()->user()->avatar) }}" />
@else
    <x-bladewind.avatar class="ring-0 border-0" size="medium" image="{{ asset('assets/icons-user.png') }}" />
@endif
