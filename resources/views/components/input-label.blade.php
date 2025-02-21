@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-extrabold text-sm text-black dark:text-gray-300']) }}>
    {{ $value ?? $slot }}
</label>
