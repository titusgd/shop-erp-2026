@props([
    'title' => null,
])

<header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-2 sm:gap-3">
        <button
            type="button"
            data-mobile-nav-open
            class="inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 lg:hidden"
            aria-label="開啟選單"
            aria-controls="mobile-nav"
            aria-expanded="false"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        @if ($title)
            <h1 class="truncate text-base font-semibold text-slate-900 sm:text-lg">{{ $title }}</h1>
        @else
            <a href="{{ route('dashboard') }}" class="truncate text-base font-semibold text-slate-900 lg:hidden">
                {{ config('app.name') }}
            </a>
        @endif
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-4">
        <div class="hidden min-w-0 text-right text-sm sm:block">
            <p class="truncate font-medium text-slate-900">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-slate-500">{{ auth()->user()->username }}</p>
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
