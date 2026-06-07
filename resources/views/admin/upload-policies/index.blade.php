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
        <h2 class="text-xl font-semibold">上传管理</h2>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button wire:click="setTab('files')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'files' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            已上传文件 ({{ $fileCount }})
        </button>
        <button wire:click="setTab('policies')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'policies' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            上传策略 ({{ $policyCounts['all'] }})
        </button>
        <button wire:click="setTab('strategies')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'strategies' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            存储策略 ({{ $strategyCounts['all'] }})
        </button>
    </div>

    @if ($tab === 'policies')
        <div class="flex items-center justify-end">
            <a href="{{ route('admin.upload-policies.create') }}"
               class="rounded-lg bg-stone-800 px-3 py-1.5 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300"
               wire:navigate>
                新增策略
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-left">
                <thead class="border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="px-4 py-3 text-sm font-medium">策略</th>
                        <th class="px-4 py-3 text-sm font-medium">存储路径</th>
                        <th class="px-4 py-3 text-sm font-medium">文件类型</th>
                        <th class="px-4 py-3 text-sm font-medium">大小限制</th>
                        <th class="px-4 py-3 text-sm font-medium">状态</th>
                        <th class="px-4 py-3 text-sm font-medium">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($policies as $policy)
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ $policy->label }}</div>
                                <code class="text-xs text-stone-500">{{ $policy->key }}</code>
                            </td>
                            <td class="px-4 py-3 text-sm text-stone-500">{{ $policy->path_prefix ?: '/' }}</td>
                            <td class="px-4 py-3 text-xs text-stone-500">{{ implode(', ', $policy->allowed_mimes) }}</td>
                            <td class="px-4 py-3 text-sm text-stone-500">{{ $policy->max_size_kb }} KB</td>
                            <td class="px-4 py-3">
                                <span class="text-xs {{ $policy->is_active ? 'text-green-600 dark:text-green-400' : 'text-stone-400' }}">
                                    {{ $policy->is_active ? '启用' : '禁用' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button wire:click="togglePolicy('{{ $policy->key }}')"
                                            class="text-xs text-stone-600 hover:underline dark:text-stone-400">
                                        {{ $policy->is_active ? '禁用' : '启用' }}
                                    </button>
                                    <span class="text-stone-300 dark:text-stone-600">·</span>
                                    <a href="{{ route('admin.upload-policies.edit', $policy) }}"
                                       class="text-xs text-stone-600 hover:underline dark:text-stone-400"
                                       wire:navigate>
                                        编辑
                                    </a>
                                    <span class="text-stone-300 dark:text-stone-600">·</span>
                                    <button wire:click="deletePolicy('{{ $policy->key }}')"
                                            wire:confirm="确定删除「{{ $policy->label }}」？"
                                            class="text-xs text-red-600 hover:underline dark:text-red-400">
                                        删除
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-stone-500">没有上传策略。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($tab === 'strategies')
        <div class="flex items-center justify-end">
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
                                    <button wire:click="toggleStrategy('{{ $strategy->key }}')"
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
                                        <button wire:click="deleteStrategy('{{ $strategy->key }}')"
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
    @endif

    @if ($tab === 'files')
        <div class="flex items-center justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="setFileFilter('all')"
                        class="rounded-lg border px-3 py-1 text-xs {{ $fileFilter === 'all' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
                    全部
                </button>
                @foreach ($allPolicies as $p)
                    <button wire:click="setFileFilter('{{ $p->key }}')"
                            class="rounded-lg border px-3 py-1 text-xs {{ $fileFilter === $p->key ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
                        {{ $p->label }}
                    </button>
                @endforeach
                <button wire:click="setFileFilter('uncategorized')"
                        class="rounded-lg border px-3 py-1 text-xs {{ $fileFilter === 'uncategorized' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
                    未分类
                </button>
            </div>
            <button wire:click="openUploadModal"
                    class="rounded-lg bg-stone-800 px-3 py-1.5 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300">
                上传文件
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="w-full text-left">
                <thead class="border-b border-neutral-200 dark:border-neutral-700">
                    <tr>
                        <th class="px-4 py-3 text-sm font-medium">文件名</th>
                        <th class="px-4 py-3 text-sm font-medium">归属策略</th>
                        <th class="px-4 py-3 text-sm font-medium">大小</th>
                        <th class="px-4 py-3 text-sm font-medium">上传时间</th>
                        <th class="px-4 py-3 text-sm font-medium">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($files as $file)
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium truncate max-w-xs" title="{{ $file['path'] }}">{{ $file['name'] }}</div>
                                <code class="text-xs text-stone-500">{{ $file['path'] }}</code>
                            </td>
                            <td class="px-4 py-3 text-xs text-stone-500">{{ $file['policy_label'] }}</td>
                            <td class="px-4 py-3 text-sm text-stone-500">{{ $file['size_readable'] }}</td>
                            <td class="px-4 py-3 text-xs text-stone-500">{{ $file['last_modified_readable'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $file['url'] }}" target="_blank"
                                       class="text-xs text-stone-600 hover:underline dark:text-stone-400">
                                        查看
                                    </a>
                                    <span class="text-stone-300 dark:text-stone-600">·</span>
                                    <button wire:click="deleteFile('{{ $file['path'] }}')"
                                            wire:confirm="确定删除「{{ $file['name'] }}」？删除后不可恢复。"
                                            class="text-xs text-red-600 hover:underline dark:text-red-400">
                                        删除
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-stone-500">没有文件。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data>
            <div class="fixed inset-0 bg-black/50" wire:click="closeUploadModal"></div>
            <div class="relative mx-4 w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold">上传文件</h3>

                <form wire:submit.prevent="doUpload" class="space-y-4">
                    <div>
                        <flux:select wire:model="uploadPolicyKey" label="上传策略" placeholder="请选择上传策略">
                            @foreach ($allPolicies as $p)
                                <option value="{{ $p->key }}">{{ $p->label }} ({{ implode(', ', $p->allowed_mimes) }} · {{ $p->max_size_kb }} KB)</option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">选择文件</label>
                        <input type="file" wire:model="uploadFile"
                               class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-stone-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-stone-700 hover:file:bg-stone-200 dark:text-stone-400 dark:file:bg-stone-700 dark:file:text-stone-300 dark:hover:file:bg-stone-600">
                        @error('uploadFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if ($uploadFile)
                        <div class="rounded-lg bg-neutral-50 px-3 py-2 text-xs text-stone-500 dark:bg-neutral-800">
                            已选择：{{ $uploadFile->getClientOriginalName() }} ({{ round($uploadFile->getSize() / 1024, 1) }} KB)
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeUploadModal"
                                class="rounded-lg px-4 py-2 text-sm text-stone-600 hover:bg-stone-50 dark:text-stone-400 dark:hover:bg-neutral-800">
                            取消
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                class="rounded-lg bg-stone-800 px-4 py-2 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300">
                            上传
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
