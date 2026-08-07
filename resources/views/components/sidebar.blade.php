@props([
    'active' => null,
])

@php
    $navItems = [
        [
            'type' => 'link',
            'key' => 'dashboard',
            'label' => '儀表板',
            'route' => 'dashboard',
            'icon' => 'dashboard',
        ],
        [
            'type' => 'link',
            'key' => 'users',
            'label' => '帳號管理',
            'route' => 'users.index',
            'icon' => 'users',
        ],
        [
            'type' => 'group',
            'label' => '廠商',
            'children' => [
                [
                    'key' => 'vendors',
                    'label' => '廠商管理',
                    'route' => 'vendors.index',
                    'icon' => 'vendors',
                ],
            ],
        ],
    ];

    $renderIcon = function (string $icon): string {
        return match ($icon) {
            'dashboard' => '<svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>',
            'users' => '<svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
            'vendors' => '<svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>',
            default => '',
        };
    };

    $isItemActive = function (array $item) use ($active): bool {
        return $active === $item['key']
            || request()->routeIs($item['route'])
            || ($item['key'] === 'users' && request()->routeIs('users.*'))
            || ($item['key'] === 'vendors' && request()->routeIs('vendors.*'));
    };

    $renderNavLink = function (array $item, bool $isActive) use ($renderIcon): string {
        $classes = $isActive
            ? 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition bg-teal-50 text-teal-800'
            : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition text-slate-600 hover:bg-slate-50 hover:text-slate-900';

        return '<a href="'.e(route($item['route'])).'" class="'.$classes.'">'
            .$renderIcon($item['icon'])
            .'<span>'.e($item['label']).'</span>'
            .'</a>';
    };
@endphp

<aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
    <div class="flex h-16 items-center border-b border-slate-200 px-6">
        <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight text-slate-900">
            {{ config('app.name') }}
        </a>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4">
        @foreach ($navItems as $item)
            @if (($item['type'] ?? 'link') === 'group')
                <div class="pt-3">
                    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {{ $item['label'] }}
                    </p>
                    <div class="space-y-1">
                        @foreach ($item['children'] as $child)
                            {!! $renderNavLink($child, $isItemActive($child)) !!}
                        @endforeach
                    </div>
                </div>
            @else
                {!! $renderNavLink($item, $isItemActive($item)) !!}
            @endif
        @endforeach
    </nav>
</aside>

<div id="mobile-nav" data-mobile-nav class="fixed inset-0 z-40 hidden lg:hidden" aria-hidden="true">
    <div data-mobile-nav-backdrop class="absolute inset-0 bg-slate-900/40"></div>

    <aside
        data-mobile-nav-panel
        class="absolute inset-y-0 left-0 flex w-72 max-w-[85vw] -translate-x-full flex-col bg-white shadow-xl transition-transform duration-200 ease-out"
    >
        <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight text-slate-900">
                {{ config('app.name') }}
            </a>
            <button
                type="button"
                data-mobile-nav-close
                class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                aria-label="關閉選單"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @foreach ($navItems as $item)
                @if (($item['type'] ?? 'link') === 'group')
                    <div class="pt-3">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ $item['label'] }}
                        </p>
                        <div class="space-y-1">
                            @foreach ($item['children'] as $child)
                                {!! $renderNavLink($child, $isItemActive($child)) !!}
                            @endforeach
                        </div>
                    </div>
                @else
                    {!! $renderNavLink($item, $isItemActive($item)) !!}
                @endif
            @endforeach
        </nav>
    </aside>
</div>
