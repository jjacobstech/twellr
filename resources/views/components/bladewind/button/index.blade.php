@props([
    // this has nothing to do HTML's button types
    // this defines if the button is primary or secondary
    'type' => 'primary',

    // tiny, small, regular, big
    'size' => config('bladewind.button.size', 'regular'),

    // for use with css and js if you want to manipulate the button
    'name' => null,

    // will show a spinner
    'has_spinner' => false,
    // for backward compatibility with Laravel 8
    'hasSpinner' => false,

    // will show a spinner
    'show_spinner' => false,
    // for backward compatibility with Laravel 8
    'showSpinner' => false,

    // Custom Prop by Jacobs Engineering to style spinner
    'customSpinnerCss' => '',
    // will make this <button type="submit">
    'can_submit' => false,
    // for backward compatibility with Laravel 8
    'canSubmit' => false,

    // set to true to disable the button
    'disabled' => false,

    // what tags to use for drawing the button <a> or <button>
    // available options are a, button
    'tag' => config('bladewind.button.tag', 'button'),

    // button colour
    'color' => '',

    // overwrite the button text color
    'button_text_css' => null,
    'buttonTextCss' => null,

    // icon to display to the left of the button
    'icon' => '',
    'icon_right' => config('bladewind.button.icon_right', false),
    'iconRight' => config('bladewind.button.icon_right', false),

    // should a ring be shown around the button?
    'show_focus_ring' => config('bladewind.button.show_focus_ring', true),
    'showFocusRing' => config('bladewind.button.show_focus_ring', true),

    // should the button be only an outline
    'outline' => config('bladewind.button.outline', false),

    // thickness of outline
    // 2, 4, 8
    // becomes border-2, border-4, border-8
    'border_width' => config('bladewind.button.border_width', 2),

    // thickness of the ring shown on focus
    // 1, 2, 4, 8
    // becomes ring, ring-1, ring-2, ring-4, ring-8
    'ring_width' => config('bladewind.button.ring_width', ''),

    // determines how rounded the button should be
    // none, small, medium, full
    'radius' => config('bladewind.button.radius', 'small'),

    // is this a circular button
    'circular' => false,

    // display button text as uppercase or as user entered
    'uppercasing' => config('bladewind.button.uppercasing', true),
])
@php
    $show_spinner = parseBladewindVariable($show_spinner);
    $showSpinner = parseBladewindVariable($showSpinner);
    $has_spinner = parseBladewindVariable($has_spinner);
    $hasSpinner = parseBladewindVariable($hasSpinner);
    $can_submit = parseBladewindVariable($can_submit);
    $canSubmit = parseBladewindVariable($canSubmit);
    $outline = parseBladewindVariable($outline);
    $uppercasing = parseBladewindVariable($uppercasing);
    $circular = parseBladewindVariable($circular);
    $show_focus_ring = parseBladewindVariable($show_focus_ring);
    $showFocusRing = parseBladewindVariable($showFocusRing);

    if ($showSpinner) {
        $show_spinner = $showSpinner;
    }
    if ($hasSpinner) {
        $has_spinner = $hasSpinner;
    }
    if ($canSubmit) {
        $can_submit = $canSubmit;
    }
    if (!$showFocusRing) {
        $show_focus_ring = $showFocusRing;
    }

    $roundness = [
        'none' => 'rounded-none',
        'small' => 'rounded-md',
        'medium' => 'rounded-xl',
        'full' => 'rounded-full',
    ];

    $icon_size = [
        'circular' => [
            'tiny' => '!size-[16px]',
            'small' => '!size-[22px]',
            'regular' => '!size-6',
            'medium' => '!size-7',
            'big' => '!size-9',
        ],
        'tiny' => '!size-3 !mt-[-2px]',
        'small' => '!size-3.5',
        'regular' => '!size-4',
        'medium' => '!size-[20px]',
        'big' => '!size-[25px]',
    ];

    $colour = !empty($color) ? $color : $type;
    $outline_colour = "border-$colour-500/50 focus:ring-$colour-500 hover:border-$colour-600
                        dark:hover:border-$colour-500 active:border-$colour-600 text-$colour-600  %s";
    $button_colour = "!bg-$colour-500 hover:!bg-$colour-600 focus:ring-$colour-500 active:bg-$colour-600 %s";

    if ($colour == 'black') {
        $outline_colour = preg_replace('/(-)?(\/)?\d+/', '', $outline_colour);
        $button_colour = preg_replace('/-\d+/', '', $button_colour);
    }
    $button_type = $can_submit ? 'submit' : 'button';
    $spinner_css = sprintf(
        $outline ? 'text-gray-600 dark:text-white %s' : 'text-white %s',
        !$show_spinner ? 'hidden' : '',
    );
    $focus_ring_width = $ring_width !== '' && in_array((int) $ring_width, [1, 2, 4, 8]) ? '-' . $ring_width : '';
    $focus_ring_css = !$show_focus_ring ? 'focus:ring-0 focus:outline-0' : 'focus:ring' . $focus_ring_width;
    $border_width = ' border-' . $border_width;
    $primary_colour_css = $outline
        ? sprintf($outline_colour, $focus_ring_css . $border_width)
        : sprintf($button_colour, $focus_ring_css);
    $radius_css = $roundness[$radius] ?? 'rounded-full';
    $button_text_css = !empty($buttonTextCss) ? $buttonTextCss : $button_text_css;
    $button_text_colour = !empty($button_text_css) ? $button_text_css : 'text-white hover:text-white';
    $disabled_css = $disabled ? 'disabled' : 'cursor-pointer';
    $outline_css = $outline ? 'outlined ' . $border_width : '';
    $has_icon_css = !empty($icon) ? ' has-icon ' : '';
    $tag = $tag !== 'a' && $tag !== 'button' ? 'button' : $tag;
    $base_button_css = $circular ? 'bw-button-circle' : 'bw-button ' . ($uppercasing ? 'uppercase ' : '');
    $merged_attributes = $attributes->merge([
        'class' => "$base_button_css $size $type $name $primary_colour_css $disabled_css $radius_css $outline_css $has_icon_css",
    ]);
    $icon_css = $circular
        ? $icon_size['circular'][$size]
        : $icon_size[$size] .
            ' dark:text-white/80 ' .
            (!$icon_right ? '!-ml-2 rtl:!-mr-2 !mr-2 rtl:!ml-2' : '!-mr-2 rtl:!-ml-2 !ml-2 rtl:!mr-2');
@endphp

<{{ $tag }} {{ $merged_attributes }} @if ($disabled) disabled @endif
    @if ($tag == 'button') type="{{ $button_type }}" @endif>
    @if (!empty($icon) && !$icon_right)
        <x-bladewind::icon :name="$icon" class="stroke-2 {{ $icon_css }}" />
    @endif
    @if (!$circular)
        <span class="grow {{ $button_text_css }}">{{ $slot }}</span>
    @endif
    @if (!empty($icon) && $icon_right && !$has_spinner)
        <x-bladewind::icon :name="$icon" class="stroke-2 {{ $icon_css }}" />
    @endif
    @if ($has_spinner)
        <x-bladewind::spinner
            class="{{ $icon_size[$size] }} !-mr-2 rtl:!-ml-2 !ml-2 rtl:!mr-2 {{ $spinner_css }} {{ $customSpinnerCss }}" />
    @endif
    </{{ $tag }}>
