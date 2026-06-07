<flux:sidebar
    sticky
    collapsible="{{ $mobileConfig['collapsible'] ?? 'mobile' }}"
    class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
    wire:ignore.self
>
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('admin.index') }}" wire:navigate />
        <flux:sidebar.collapse class="{{ ($mobileConfig['breakpoint'] ?? 'lg') . ':hidden' }}" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        @foreach ($filteredGroups as $group)
            <flux:sidebar.group
                :heading="$group['heading']"
                class="grid"
                :expandable="$group['collapsible']"
                :expanded="!($group['default_collapsed'] ?? false)"
            >
                @foreach ($group['items'] as $item)
                    @include('components.sidebar-item', ['item' => $item, 'groupHeading' => $group['heading']])
                @endforeach
            </flux:sidebar.group>
        @endforeach
    </flux:sidebar.nav>

    <flux:spacer />

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>

@push('scripts')
<script>
    document.addEventListener('livewire:navigated', () => {
        const sidebar = document.querySelector('[flux\\:sidebar]');
        if (sidebar && sidebar._fluxSidebar) {
            sidebar._fluxSidebar.close();
        }
    });
</script>
@endpush