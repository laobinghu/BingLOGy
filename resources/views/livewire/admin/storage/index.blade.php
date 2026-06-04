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
        <h2 class="text-xl font-semibold">存储管理</h2>
        <a href="{{ route('admin.storage.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700" wire:navigate>
            + 新增存储
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="w-full text-left">
            <thead class="border-b border-neutral-200 dark:border-neutral-700">
                <tr>
                    <th class="px-4 py-3 text-sm font-medium">名称</th>
                    <th class="px-4 py-3 text-sm font-medium">驱动</th>
                    <th class="px-4 py-3 text-sm font-medium">状态</th>
                    <th class="px-4 py-3 text-sm font-medium">默认</th>
                    <th class="px-4 py-3 text-sm font-medium">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($disks as $disk)
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-3 font-medium">{{ $disk->name }}</td>
                        <td class="px-4 py-3 text-sm uppercase">{{ $disk->driver }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleAvailability({{ $disk->id }})"
                                    class="inline-flex items-center gap-1.5 text-sm">
                                @if ($disk->is_available)
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                    <span class="text-green-600 dark:text-green-400">已启用</span>
                                @else
                                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                    <span class="text-red-600 dark:text-red-400">已禁用</span>
                                @endif
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            @if ($disk->is_default)
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">默认</span>
                            @else
                                <button wire:click="setDefault({{ $disk->id }})"
                                        class="text-xs text-neutral-500 hover:text-blue-600 dark:hover:text-blue-400">
                                    设为默认
                                </button>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button wire:click="testConnection({{ $disk->id }})"
                                        class="text-sm text-neutral-600 hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400"
                                        wire:loading.attr="disabled">
                                    测试连接
                                </button>
                                <span class="text-neutral-300 dark:text-neutral-600">·</span>
                                <a href="{{ route('admin.storage.edit', $disk) }}"
                                   class="text-sm text-neutral-600 hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400" wire:navigate>
                                    编辑
                                </a>
                                @if (!$disk->is_default)
                                    <span class="text-neutral-300 dark:text-neutral-600">·</span>
                                    <button wire:click="delete({{ $disk->id }})"
                                            wire:confirm="确定删除「{{ $disk->name }}」？"
                                            class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                        删除
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-neutral-500">
                            还没有存储配置。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
