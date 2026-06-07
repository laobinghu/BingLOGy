<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <div class="mb-2 flex items-center justify-between">
        <h2 class="text-xl font-semibold">{{ $isEditing ? '编辑存储' : '新增存储' }}</h2>
        <a href="{{ route('admin.storage.index') }}"
           class="text-sm text-neutral-600 hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400" wire:navigate>
            ← 返回列表
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h3 class="mb-4 text-sm font-medium">基本信息</h3>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">名称</label>
                <input type="text" wire:model="name"
                       class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">驱动</label>
                <select wire:model.live="driver"
                        class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                    <option value="local">Local（本地）</option>
                    <option value="oss">OSS（阿里云）</option>
                    <option value="cos">COS（腾讯云）</option>
                    <option value="s3">S3（兼容）</option>
                </select>
                @error('driver') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h3 class="mb-4 text-sm font-medium">驱动配置</h3>

            @if ($driver === 'local')
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">存储路径</label>
                    <input type="text" wire:model="config_path"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700"
                           placeholder="storage/app/public">
                    @error('config_path') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            @if (in_array($driver, ['oss', 's3']))
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Access Key ID</label>
                    <input type="password" wire:model="config_access_key_id"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                    @error('config_access_key_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Access Key Secret</label>
                    <input type="password" wire:model="config_access_key_secret"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                    @error('config_access_key_secret') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            @if (in_array($driver, ['oss', 'cos', 's3']))
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Bucket</label>
                    <input type="text" wire:model="config_bucket"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                    @error('config_bucket') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            @if (in_array($driver, ['oss', 's3']))
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Endpoint</label>
                    <input type="text" wire:model="config_endpoint"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                    @error('config_endpoint') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            @if (in_array($driver, ['cos', 's3']))
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Region</label>
                    <input type="text" wire:model="config_region"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700"
                           placeholder="ap-guangzhou / us-east-1">
                    @error('config_region') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            @if ($driver === 'cos')
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Secret ID</label>
                    <input type="password" wire:model="config_secret_id"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                    @error('config_secret_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Secret Key</label>
                    <input type="password" wire:model="config_secret_key"
                           class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">
                    @error('config_secret_key') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            @if ($driver === 's3')
                <div class="mb-4 flex items-center gap-2">
                    <input type="checkbox" id="use_path_style" wire:model="config_use_path_style"
                           class="rounded border-neutral-300 dark:border-neutral-700">
                    <label for="use_path_style" class="text-sm">使用 Path Style 端点</label>
                </div>
            @endif

            @if (in_array($driver, ['oss', 'cos']))
                <div class="mb-4 flex items-center gap-2">
                    <input type="checkbox" id="use_cdn" wire:model.live="config_use_cdn"
                           class="rounded border-neutral-300 dark:border-neutral-700">
                    <label for="use_cdn" class="text-sm">启用 CDN</label>
                </div>

                @if ($config_use_cdn)
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">CDN 域名</label>
                        <input type="text" wire:model="config_cdn_domain"
                               class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700"
                               placeholder="https://cdn.example.com">
                        @error('config_cdn_domain') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                {{ $isEditing ? '保存修改' : '创建存储' }}
            </button>
            <a href="{{ route('admin.storage.index') }}"
               class="text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100" wire:navigate>
                取消
            </a>
        </div>
    </form>
</div>
