@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm app-title']) }}>
    {{ $value ?? $slot }}
</label>
