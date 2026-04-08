@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'app-input rounded-xl px-4 py-3 shadow-sm']) }}>
