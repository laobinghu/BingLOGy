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
            @include('components.sidebar-item', ['item' => $child])
        @endforeach
    </flux:sidebar.nav>
@else
    <flux:sidebar.item
        icon="{{ $item['icon'] }}"
        :href="$href"
        :current="$isCurrent"
        target="{{ $target }}"
        wire:navigate="{{ $shouldNavigate }}"
    >
        {{ $item['label'] }}
        @if ($badge)
            <flux:badge class="ml-2" size="sm">{{ $badge }}</flux:badge>
        @endif
    </flux:sidebar.item>
@endif