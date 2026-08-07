<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @isset($title)
            {{ $title }} — {{ config('app.name') }}
        @else
            {{ config('app.name') }}
        @endisset
    </title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="flex min-h-screen">
        <x-sidebar :active="$active ?? null" />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-header :title="$title ?? null" />

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @isset($scripts)
        {{ $scripts }}
    @endisset
    @stack('scripts')
</body>
</html>
