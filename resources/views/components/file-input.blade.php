@props(['disabled' => false, 'type' => ''])

<input type="{{ $type }}" @disabled($disabled)
    {{ $attributes->merge(['class' => 'border-gray-500 focus:border-navy-blue focus:ring-navy-blue rounded-md shadow-sm']) }} />
