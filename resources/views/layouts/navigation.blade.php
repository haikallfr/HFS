@php($navigation = \App\Support\HfsNavigation::forUser(auth()->user()))

<nav x-data="{ open: false }" class="border-b" style="border-color: var(--border); background: color-mix(in srgb, var(--surface) 88%, transparent); backdrop-filter: blur(10px);">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('dashboard') }}">
                <x-application-logo class="h-11" />
            </a>

            <div class="hidden items-center gap-2 lg:flex">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}"
                       class="rounded-full px-4 py-2 text-sm font-medium transition"
                       style="{{ $item['active'] ? 'background: var(--accent); color: white;' : 'color: var(--text-muted);' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="hidden items-center gap-4 lg:flex">
            <a href="{{ route('profile.edit') }}" class="app-button-secondary rounded-full px-4 py-2 text-sm">
                Profil
            </a>
            <div class="app-panel-strong rounded-2xl px-4 py-2 text-right">
                <div class="text-sm font-medium app-title">{{ auth()->user()->name }}</div>
                <div class="text-xs app-muted">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'Tanpa role' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full px-4 py-2 text-sm font-semibold" style="background: var(--danger); color: white;">
                    Keluar
                </button>
            </form>
        </div>

        <button @click="open = !open" class="app-button-secondary inline-flex items-center justify-center rounded-xl p-2 lg:hidden">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div x-cloak :class="{'block': open, 'hidden': ! open}" class="hidden px-4 pb-4 lg:hidden" style="border-top: 1px solid var(--border);">
        <div class="mt-4 grid gap-2">
            @foreach ($navigation as $item)
                <a href="{{ route($item['route']) }}" class="rounded-2xl px-4 py-3 text-sm" style="{{ $item['active'] ? 'background: var(--accent); color: white;' : 'background: var(--surface-strong); color: var(--text); border: 1px solid var(--border);' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('profile.edit') }}" class="app-button-secondary rounded-2xl px-4 py-3 text-sm">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-semibold" style="background: var(--danger); color: white;">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</nav>
