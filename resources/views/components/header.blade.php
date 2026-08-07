@props([
    'title' => null,
])

<header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-base font-semibold text-slate-900 lg:hidden">
            {{ config('app.name') }}
        </a>
        @if ($title)
            <h1 class="hidden text-lg font-semibold text-slate-900 sm:block lg:block">{{ $title }}</h1>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <div class="hidden text-right text-sm sm:block">
            <p class="font-medium text-slate-900">{{ auth()->user()->name }}</p>
            <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                登出
            </button>
        </form>
    </div>
</header>
