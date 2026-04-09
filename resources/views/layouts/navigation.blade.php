@php($navigation = \App\Support\HfsNavigation::forUser(auth()->user()))

<nav x-data="{ open: false }" class="px-4 pt-6 sm:px-6 lg:px-8">
    <div class="app-panel mx-auto flex max-w-7xl items-center justify-between gap-6 rounded-[28px] px-5 py-4">
        <div class="flex min-w-0 items-center gap-5">
            <a href="{{ route('dashboard') }}" class="shrink-0">
                <x-application-logo class="h-11" />
            </a>

            <div class="hidden items-center gap-2 xl:flex">
                @foreach ($navigation as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="rounded-full px-4 py-2 text-sm font-medium transition"
                        style="{{ $item['active'] ? 'background: var(--text); color: var(--surface-strong);' : 'color: var(--text-muted);' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="hidden items-center gap-3 xl:flex">
            <a href="{{ route('profile.edit') }}" class="app-button-secondary rounded-full px-4 py-2 text-sm">
                Profil
            </a>

            <div class="rounded-2xl px-4 py-2 text-right" style="background: var(--surface-muted);">
                <div class="text-sm font-semibold app-title">{{ auth()->user()->name }}</div>
                <div class="text-xs app-muted">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'Tanpa role' }}</div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-button rounded-full px-4 py-2 text-sm font-semibold">
                    Keluar
                </button>
            </form>
        </div>

        <button @click="open = !open" class="app-button-secondary inline-flex items-center justify-center rounded-2xl p-2 xl:hidden">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div x-cloak :class="{'block': open, 'hidden': ! open}" class="mx-auto hidden max-w-7xl pt-3 xl:hidden">
        <div class="app-panel rounded-[28px] px-4 py-4">
            <div class="grid gap-2">
                @foreach ($navigation as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="rounded-2xl px-4 py-3 text-sm font-medium"
                        style="{{ $item['active'] ? 'background: var(--text); color: var(--surface-strong);' : 'background: var(--surface-strong); color: var(--text); border: 1px solid var(--border);' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <a href="{{ route('profile.edit') }}" class="app-button-secondary rounded-2xl px-4 py-3 text-sm">
                    Profil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="app-button w-full rounded-2xl px-4 py-3 text-sm font-semibold">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
