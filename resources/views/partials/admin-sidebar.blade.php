@php
    $resolver = app(\App\Services\MenuResolver::class);
    $menuGroups = $resolver->resolve();
    $mobileConfig = $resolver->getMobileConfig();
@endphp

<flux:sidebar
    sticky
    collapsible="{{ $mobileConfig['collapsible'] ?? 'mobile' }}"
    class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
>
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('admin.index') }}" wire:navigate />
        <flux:sidebar.collapse class="{{ ($mobileConfig['breakpoint'] ?? 'lg') . ':hidden' }}" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        @foreach ($menuGroups as $group)
            <flux:sidebar.group
                :heading="$group['heading']"
                class="grid"
                :expandable="$group['collapsible']"
                :expanded="!($group['default_collapsed'] ?? false)"
            >
                @foreach ($group['items'] as $item)
                    @php
                        $hasChildren = $item['has_children'] ?? false;
                        $children = $item['children'] ?? [];
                        $isCurrent = $item['current'] && request()->routeIs($item['current']);
                        $href = $item['route'] ? route($item['route']) : ($item['url'] ?? '#');
                        $target = $item['target'] ?? '_self';
                        $badge = $item['badge'] ?? null;
                        $shouldNavigate = !$item['route'];
                    @endphp

                    @if ($hasChildren)
                        <flux:sidebar.item
                            icon="{{ $item['icon'] }}"
                            :href="$href"
                            :current="$isCurrent"
                            target="{{ $target }}"
                            wire:navigate="{{ $shouldNavigate }}"
                            class="flex items-center justify-between"
                        >
                            {{ $item['label'] }}
                            @if ($badge)
                                <flux:badge class="ml-2" size="sm">{{ $badge }}</flux:badge>
                            @endif
                            <flux:icon name="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform duration-200 group-open:rotate-180" />
                        </flux:sidebar.item>

                        <flux:sidebar.nav class="ml-6 mt-1 space-y-0.5 border-l border-zinc-200 dark:border-zinc-700 pl-3">
                            @foreach ($children as $child)
                                @php
                                    $childHasChildren = $child['has_children'] ?? false;
                                    $childChildren = $child['children'] ?? [];
                                    $childIsCurrent = $child['current'] && request()->routeIs($child['current']);
                                    $childHref = $child['route'] ? route($child['route']) : ($child['url'] ?? '#');
                                    $childTarget = $child['target'] ?? '_self';
                                    $childBadge = $child['badge'] ?? null;
                                    $childShouldNavigate = !$child['route'];
                                @endphp

                                @if ($childHasChildren)
                                    <flux:sidebar.item
                                        icon="{{ $child['icon'] }}"
                                        :href="$childHref"
                                        :current="$childIsCurrent"
                                        target="{{ $childTarget }}"
                                        wire:navigate="{{ $childShouldNavigate }}"
                                        class="flex items-center justify-between"
                                    >
                                        {{ $child['label'] }}
                                        @if ($childBadge)
                                            <flux:badge class="ml-2" size="sm">{{ $childBadge }}</flux:badge>
                                        @endif
                                        <flux:icon name="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform duration-200 group-open:rotate-180" />
                                    </flux:sidebar.item>

                                    <flux:sidebar.nav class="ml-6 mt-1 space-y-0.5 border-l border-zinc-200 dark:border-zinc-700 pl-3">
                                        @foreach ($childChildren as $grandChild)
                                            @php
                                                $grandChildIsCurrent = $grandChild['current'] && request()->routeIs($grandChild['current']);
                                                $grandChildHref = $grandChild['route'] ? route($grandChild['route']) : ($grandChild['url'] ?? '#');
                                                $grandChildTarget = $grandChild['target'] ?? '_self';
                                                $grandChildShouldNavigate = !$grandChild['route'];
                                            @endphp

                                            <flux:sidebar.item
                                                icon="{{ $grandChild['icon'] }}"
                                                :href="$grandChildHref"
                                                :current="$grandChildIsCurrent"
                                                wire:navigate="{{ $grandChildShouldNavigate }}"
                                                target="{{ $grandChildTarget }}"
                                            >
                                                {{ $grandChild['label'] }}
                                            </flux:sidebar.item>
                                        @endforeach
                                    </flux:sidebar.nav>
                                @else
                                    <flux:sidebar.item
                                        icon="{{ $child['icon'] }}"
                                        :href="$childHref"
                                        :current="$childIsCurrent"
                                        wire:navigate="{{ $childShouldNavigate }}"
                                        target="{{ $childTarget }}"
                                    >
                                        {{ $child['label'] }}
                                        @if ($childBadge)
                                            <flux:badge class="ml-2" size="sm">{{ $childBadge }}</flux:badge>
                                        @endif
                                    </flux:sidebar.item>
                                @endif
                            @endforeach
                        </flux:sidebar.nav>
                    @else
                        <flux:sidebar.item
                            icon="{{ $item['icon'] }}"
                            :href="$href"
                            :current="$isCurrent"
                            wire:navigate="{{ $shouldNavigate }}"
                            target="{{ $target }}"
                        >
                            {{ $item['label'] }}
                            @if ($badge)
                                <flux:badge class="ml-2" size="sm">{{ $badge }}</flux:badge>
                            @endif
                        </flux:sidebar.item>
                    @endif
                @endforeach
            </flux:sidebar.group>
        @endforeach
    </flux:sidebar.nav>

    <flux:spacer />

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>