<button {{ $attributes->merge(['type' => 'submit', 'class' => 'app-button inline-flex items-center rounded-xl px-5 py-3 font-semibold text-sm transition ease-in-out duration-150 focus:outline-none']) }}>
    {{ $slot }}
</button>
