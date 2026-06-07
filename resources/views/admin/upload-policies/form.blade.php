<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.upload-policies.index') }}"
               class="text-sm text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200"
               wire:navigate>
                ← 返回
            </a>
            <h2 class="text-xl font-semibold">{{ $isEditing ? '编辑上传策略' : '新增上传策略' }}</h2>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h3 class="mb-4 text-sm font-medium">基本信息</h3>

            <div class="mb-4">
                <flux:input wire:model="key" label="策略标识" placeholder="例：cover_image、attachment"
                            :disabled="$isEditing" />
                @error('key') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-neutral-400">唯一标识符，创建后不可修改。建议使用 snake_case。</p>
            </div>

            <div class="mb-4">
                <flux:input wire:model="label" label="显示名称" placeholder="例：文章封面图" />
                @error('label') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <flux:select wire:model="storage_strategy_id" label="绑定存储策略" placeholder="不绑定 (默认本地 public 盘)">
                    @foreach (\App\Models\StorageStrategy::where('is_active', true)->get() as $strategy)
                        <option value="{{ $strategy->id }}">{{ $strategy->label }} ({{ $strategy->driver }})</option>
                    @endforeach
                </flux:select>
                @error('storage_strategy_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-neutral-400">选择在「存储策略」页面中配置的后端。不绑定则使用本地 public 盘。</p>
            </div>

            <div class="mb-4">
                <flux:input wire:model="path_prefix" label="存储路径前缀" placeholder="例：covers、attachments" />
                @error('path_prefix') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-neutral-400">文件将存储在此子目录下，留空则直接存根目录。</p>
            </div>

            <div class="mb-4">
                <flux:input wire:model="max_size_kb" label="最大文件大小 (KB)" type="number" />
                @error('max_size_kb') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <flux:switch wire:model="is_active" label="启用此策略" description="禁用后相关类型上传将不再受此策略约束。" />
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h3 class="mb-4 text-sm font-medium">允许的文件类型</h3>

            <div class="flex flex-wrap gap-2">
                @php
                    $allMimes = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'zip', 'pdf'];
                @endphp
                @foreach ($allMimes as $mime)
                    <button type="button" wire:click="toggleMime('{{ $mime }}')"
                            class="rounded-full px-3 py-1 text-xs font-medium transition-all {{ in_array($mime, $allowed_mimes)
                                ? 'bg-stone-800 text-white dark:bg-stone-200 dark:text-stone-800'
                                : 'border border-stone-200 text-stone-600 hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800/50' }}">
                        .{{ $mime }}
                    </button>
                @endforeach
            </div>
            @error('allowed_mimes') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" wire:confirm="确定{{ $isEditing ? '保存修改' : '创建策略' }}？"
                    class="rounded-lg bg-stone-800 px-4 py-2 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300">
                {{ $isEditing ? '保存修改' : '创建策略' }}
            </button>
            <a href="{{ route('admin.upload-policies.index') }}"
               class="text-sm text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200"
               wire:navigate>
                取消
            </a>
        </div>
    </form>
</div>
