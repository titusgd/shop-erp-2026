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
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-24 top-0 h-72 w-72 rounded-full bg-teal-200/40 blur-3xl"></div>
            <div class="absolute -right-16 bottom-0 h-80 w-80 rounded-full bg-sky-200/30 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md">
            <div class="mb-8 text-center">
                <p class="text-2xl font-semibold tracking-tight text-slate-900">{{ config('app.name') }}</p>
                @isset($heading)
                    <h1 class="mt-2 text-sm text-slate-500">{{ $heading }}</h1>
                @endisset
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
