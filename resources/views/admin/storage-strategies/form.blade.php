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
            <h2 class="text-xl font-semibold">{{ $isEditing ? '编辑存储策略' : '新增存储策略' }}</h2>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h3 class="mb-4 text-sm font-medium">基本信息</h3>

            <div class="mb-4">
                <flux:input wire:model="key" label="策略标识" placeholder="例：local、oss、s3"
                            :disabled="$isEditing" />
                @error('key') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-neutral-400">唯一标识符，创建后不可修改。建议使用 snake_case。</p>
            </div>

            <div class="mb-4">
                <flux:input wire:model="label" label="显示名称" placeholder="例：本地存储、阿里云 OSS" />
                @error('label') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <flux:select wire:model.live="driver" label="存储驱动">
                    <option value="local">local (本地存储)</option>
                    <option value="s3">s3 (AWS S3 / 兼容)</option>
                    <option value="oss">oss (阿里云 OSS)</option>
                    <option value="cos">cos (腾讯云 COS)</option>
                </flux:select>
                @error('driver') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-neutral-400">选择 s3/oss/cos 时需填写下方凭证。</p>
            </div>

            <div class="mb-4">
                <flux:switch wire:model="is_active" label="启用此策略" description="禁用后不可被上传策略引用。" />
            </div>
        </div>

        @if (in_array($driver, ['s3', 'oss', 'cos']))
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <h3 class="mb-4 text-sm font-medium">
                    {{ match($driver) { 's3' => 'AWS S3 配置', 'oss' => '阿里云 OSS 配置', 'cos' => '腾讯云 COS 配置' } }}
                </h3>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <flux:input wire:model="config.key" label="Access Key / Secret ID" placeholder="AccessKeyID / SecretId" />
                    </div>
                    <div>
                        <flux:input wire:model="config.secret" label="Secret Key" type="password" placeholder="AccessKeySecret / SecretKey" />
                    </div>
                    <div>
                        <flux:input wire:model="config.region" label="Region" placeholder="例：ap-southeast-1 / oss-cn-hangzhou" />
                    </div>
                    <div>
                        <flux:input wire:model="config.bucket" label="Bucket" placeholder="存储桶名称" />
                    </div>
                    <div>
                        <flux:input wire:model="config.endpoint" label="Endpoint" placeholder="例：s3.amazonaws.com / oss-cn-hangzhou.aliyuncs.com" />
                    </div>
                    <div>
                        <flux:input wire:model="config.url" label="自定义域名 / CDN (可选)" placeholder="https://cdn.example.com" />
                    </div>
                    @if($driver === 's3')
                        <div class="md:col-span-2">
                            <flux:switch wire:model="config.use_path_style_endpoint" label="强制 Path-Style 端点" description="MinIO 等兼容存储需开启" />
                        </div>
                    @endif
                </div>
            </div>
        @endif

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
