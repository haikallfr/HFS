<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#171717">
        <link rel="manifest" href="/manifest.webmanifest">

        <title>{{ config('app.name', 'HFS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="app-shell">
            @include('layouts.navigation')

            @isset($header)
                <header class="mx-auto mt-6 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="app-panel rounded-[28px] px-6 py-6">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
