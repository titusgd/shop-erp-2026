@foreach ($navItems as $item)
    @if (($item['type'] ?? 'link') === 'group')
        @php
            $groupOpen = $isGroupOpen($item);
            $panelId = 'nav-group-'.($item['key'] ?? uniqid()).'-'.uniqid();
        @endphp
        <div
            class="pt-3"
            data-nav-group
            data-nav-group-key="{{ $item['key'] ?? '' }}"
            data-open="{{ $groupOpen ? 'true' : 'false' }}"
        >
            <button
                type="button"
                data-nav-group-toggle
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400 transition hover:bg-slate-50 hover:text-slate-600"
                aria-expanded="{{ $groupOpen ? 'true' : 'false' }}"
                aria-controls="{{ $panelId }}"
            >
                <span>{{ $item['label'] }}</span>
                <span
                    data-nav-group-chevron
                    @class([
                        'inline-flex text-slate-400 transition-transform duration-200',
                        'rotate-180' => $groupOpen,
                    ])
                >
                    {!! $renderIcon('chevron-down') !!}
                </span>
            </button>
            <div
                id="{{ $panelId }}"
                data-nav-group-panel
                @class(['space-y-1', 'hidden' => ! $groupOpen])
            >
                @foreach ($item['children'] as $child)
                    {!! $renderNavLink($child, $isItemActive($child)) !!}
                @endforeach
            </div>
        </div>
    @else
        {!! $renderNavLink($item, $isItemActive($item)) !!}
    @endif
@endforeach
