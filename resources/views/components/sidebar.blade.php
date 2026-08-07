@props([
    'active' => null,
])

@php
    $navItems = [
        [
            'key' => 'dashboard',
            'label' => '儀表板',
            'route' => 'dashboard',
            'icon' => 'dashboard',
        ],
        [
            'key' => 'users',
            'label' => '帳號管理',
            'route' => 'users.index',
            'icon' => 'users',
        ],
    ];
@endphp

<aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
    <div class="flex h-16 items-center border-b border-slate-200 px-6">
        <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight text-slate-900">
            {{ config('app.name') }}
        </a>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4">
        @foreach ($navItems as $item)
            @php
                $isActive = $active === $item['key']
                    || request()->routeIs($item['route'])
                    || ($item['key'] === 'users' && request()->routeIs('users.*'));
            @endphp
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                    'bg-teal-50 text-teal-800' => $isActive,
                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $isActive,
                ])
            >
                @if ($item['icon'] === 'dashboard')
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 8.25 20.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                @elseif ($item['icon'] === 'users')
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                @endif
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
