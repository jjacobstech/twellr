@props(['disabled' => false])

<textarea @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'border-[#bebebe] bg-[#bebebe] focus:border-navy-blue
                    focus:ring-navy-blue rounded-md shadow-sm',
    ]) }}></textarea>
