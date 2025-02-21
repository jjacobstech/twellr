@if (auth()->user()->avatar)
<img class="w-10 h-10 rounded-full" src="{{ auth()->user()->avatar}}" title="{{ auth()->user()->name }}">
@else
<img class="w-10 h-10 rounded-full" src="{{ asset('assets/icons-user.png') }}" title="{{ auth()->user()->name }}">
@endif
