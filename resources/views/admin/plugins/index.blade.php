<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('success'))
        <div class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
            <flux:icon.check class="size-4 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400">
            <flux:icon.x-mark class="size-4 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">插件管理</h2>
        @if (!empty($plugins))
            <span class="text-xs text-neutral-400">{{ count($plugins) }} 个插件</span>
        @endif
    </div>

    @if (empty($plugins))
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-neutral-300 p-12 dark:border-neutral-700">
            <flux:icon.puzzle-piece class="mb-3 size-10 text-neutral-300 dark:text-neutral-600" />
            <p class="text-sm text-neutral-500">没有发现任何插件</p>
            <p class="mt-1 text-xs text-neutral-400">将插件放置于 <code class="rounded bg-neutral-100 px-1.5 py-0.5 font-mono text-xs dark:bg-neutral-800">plugins/</code> 目录下即可自动发现</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($plugins as $plugin)
                <div class="group rounded-xl border border-neutral-200 bg-white p-5 transition-all hover:border-neutral-300 hover:shadow-sm dark:border-neutral-700 dark:bg-transparent dark:hover:border-neutral-600">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <h3 class="text-sm font-semibold">{{ $plugin['title'] }}</h3>
                                <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                                    v{{ $plugin['version'] }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium {{ $plugin['is_active']
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                                    : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-500' }}">
                                    @if ($plugin['is_active'])
                                        <flux:icon.check class="size-3" />
                                        已启用
                                    @else
                                        <flux:icon.x-mark class="size-3" />
                                        已禁用
                                    @endif
                                </span>
                            </div>

                            @if ($plugin['description'])
                                <p class="mt-1.5 text-sm text-neutral-600 dark:text-neutral-400">{{ $plugin['description'] }}</p>
                            @endif

                            @if ($plugin['author'])
                                <p class="mt-1 text-xs text-neutral-400">作者：{{ $plugin['author'] }}</p>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <button wire:click="toggle('{{ $plugin['name'] }}')"
                                    wire:loading.attr="disabled"
                                    class="relative rounded-lg px-3 py-1.5 text-xs font-medium transition-all {{ $plugin['is_active']
                                        ? 'border border-neutral-200 text-neutral-600 hover:border-neutral-300 hover:bg-neutral-50 active:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-400 dark:hover:border-neutral-600 dark:hover:bg-neutral-800/50'
                                        : 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 dark:bg-blue-500 dark:hover:bg-blue-600' }}">
                                <span wire:loading.remove wire:target="toggle('{{ $plugin['name'] }}')">
                                    {{ $plugin['is_active'] ? '禁用' : '启用' }}
                                </span>
                                <span wire:loading wire:target="toggle('{{ $plugin['name'] }}')" class="flex items-center gap-1.5">
                                    <svg class="size-3 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    处理中
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
