@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block font-extrabold text-sm text-black dark:text-gray-300']) }}>
    {{ $value ?? $slot }} @if ($required)
        <span class="text-red-600"> *</span>
    @endif
</label>
