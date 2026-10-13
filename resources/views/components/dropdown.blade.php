@props(['align' => 'right', 'width' => '48'])

@php
    $alignmentClasses = [
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        'right' => 'ltr:origin-top-right rtl:origin-top-left end-0',
    ];
    $widthClasses = [
        '48' => 'w-48',
    ];
    $alignment = $alignmentClasses[$align] ?? $alignmentClasses['right'];
    $dropdownWidth = $widthClasses[$width] ?? $widthClasses['48'];
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $dropdownWidth }} rounded-md shadow-lg {{ $alignment }}"
        @click="open = false"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 bg-white">
            {{ $content }}
        </div>
    </div>
</div>
