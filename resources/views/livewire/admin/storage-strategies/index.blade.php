<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-2 flex items-center justify-between">
        <h2 class="text-xl font-semibold">存储策略</h2>
        <a href="{{ route('admin.storage-strategies.create') }}"
           class="rounded-lg bg-stone-800 px-3 py-1.5 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300"
           wire:navigate>
            新增存储
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="w-full text-left">
            <thead class="border-b border-neutral-200 dark:border-neutral-700">
                <tr>
                    <th class="px-4 py-3 text-sm font-medium">策略</th>
                    <th class="px-4 py-3 text-sm font-medium">驱动</th>
                    <th class="px-4 py-3 text-sm font-medium">配置</th>
                    <th class="px-4 py-3 text-sm font-medium">状态</th>
                    <th class="px-4 py-3 text-sm font-medium">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($strategies as $strategy)
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $strategy->label }}</div>
                            <code class="text-xs text-stone-500">{{ $strategy->key }}</code>
                            @if ($strategy->is_default)
                                <span class="ml-1 text-xs text-blue-600 dark:text-blue-400">默认</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-stone-500 capitalize">{{ $strategy->driver }}</td>
                        <td class="px-4 py-3 text-xs text-stone-500">
                            @if ($strategy->driver !== 'local')
                                Bucket: {{ $strategy->config['bucket'] ?? '—' }}
                                · Region: {{ $strategy->config['region'] ?? '—' }}
                                @if (!empty($strategy->config['url']))
                                    · CDN: {{ $strategy->config['url'] }}
                                @endif
                            @else
                                本地存储
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs {{ $strategy->is_active ? 'text-green-600 dark:text-green-400' : 'text-stone-400' }}">
                                {{ $strategy->is_active ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if (!$strategy->is_default)
                                    <button wire:click="setDefault('{{ $strategy->key }}')"
                                            class="text-xs text-stone-600 hover:underline dark:text-stone-400">
                                        设为默认
                                    </button>
                                    <span class="text-stone-300 dark:text-stone-600">·</span>
                                @endif
                                <button wire:click="toggle('{{ $strategy->key }}')"
                                        class="text-xs text-stone-600 hover:underline dark:text-stone-400">
                                    {{ $strategy->is_active ? '禁用' : '启用' }}
                                </button>
                                <span class="text-stone-300 dark:text-stone-600">·</span>
                                <a href="{{ route('admin.storage-strategies.edit', $strategy) }}"
                                   class="text-xs text-stone-600 hover:underline dark:text-stone-400"
                                   wire:navigate>
                                    编辑
                                </a>
                                @if (!$strategy->is_default)
                                    <span class="text-stone-300 dark:text-stone-600">·</span>
                                    <button wire:click="delete('{{ $strategy->key }}')"
                                            wire:confirm="确定删除「{{ $strategy->label }}」？"
                                            class="text-xs text-red-600 hover:underline dark:text-red-400">
                                        删除
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-stone-500">没有存储策略。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
