@props(['for', 'value' => null])

<label {{ $attributes->merge(['for' => $for, 'class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
