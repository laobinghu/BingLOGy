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
        <h2 class="text-xl font-semibold">博客设置</h2>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button wire:click="setTab('basic')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'basic' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            基本信息
        </button>
        <button wire:click="setTab('appearance')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'appearance' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            外观 / 主题
        </button>
        <button wire:click="setTab('features')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'features' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            功能开关
        </button>
    </div>

    <form wire:submit="save" class="space-y-6">
        @if ($tab === 'basic')
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <h3 class="mb-4 text-sm font-medium">站点信息</h3>

                <div class="mb-4">
                    <flux:input wire:model="site_name" label="站点名称" />
                    @error('site_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <flux:textarea wire:model="site_description" label="站点描述" rows="3" />
                </div>

                <div class="mb-4">
                    <flux:input wire:model="site_logo_url" label="Logo URL" placeholder="https://example.com/logo.png" />
                </div>

                <div class="mb-4">
                    <flux:input wire:model="favicon_url" label="Favicon URL" placeholder="https://example.com/favicon.ico" />
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <h3 class="mb-4 text-sm font-medium">SEO</h3>

                <div class="mb-4">
                    <flux:input wire:model="seo_keywords" label="关键词" placeholder="blog, technology, laravel" />
                </div>

                <div class="mb-4">
                    <flux:textarea wire:model="custom_head" label="自定义 Head 代码" rows="4"
                                  class="font-mono"
                                  placeholder="&lt;meta name=&quot;google-site-verification&quot; content=&quot;...&quot; /&gt;" />
                </div>
            </div>
        @endif

        @if ($tab === 'appearance')
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <h3 class="mb-4 text-sm font-medium">显示设置</h3>

                <div class="mb-4">
                    <flux:input wire:model="posts_per_page" label="每页文章数" type="number" />
                    @error('posts_per_page') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <flux:select wire:model="theme_default" label="默认主题模式">
                        <option value="auto">跟随系统</option>
                        <option value="dark">深色</option>
                        <option value="light">浅色</option>
                    </flux:select>
                </div>

                <div class="mb-4">
                    <flux:select wire:model="theme_current" label="当前主题" placeholder="默认主题">
                        @foreach ($this->themes as $theme)
                            <option value="{{ $theme }}">{{ $theme }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="mb-4">
                    <flux:textarea wire:model="custom_css" label="自定义 CSS" rows="4" class="font-mono" />
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <h3 class="mb-4 text-sm font-medium">上传主题</h3>

                <input type="file" wire:model="themeZip" accept=".zip"
                       class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-stone-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-stone-700 hover:file:bg-stone-200 dark:text-stone-400 dark:file:bg-stone-800 dark:file:text-stone-300 dark:hover:file:bg-stone-700">
                @error('themeZip') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                <button type="button" wire:click="uploadThemeZip"
                        class="mt-2 rounded-lg bg-stone-800 px-4 py-2 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300">
                    上传并解压
                </button>
                <p class="mt-1 text-xs text-neutral-500">支持的格式：.zip，将解压至 resources/views/themes/ 目录下。</p>
            </div>
        @endif

        @if ($tab === 'features')
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <h3 class="mb-4 text-sm font-medium">功能开关</h3>

                <div class="space-y-4">
                    <flux:switch wire:model="allow_registration" label="允许注册" description="开启后允许新用户注册账号" />
                    <flux:switch wire:model="public_access" label="公开访问" description="关闭后前台需要登录才能访问" />
                    <flux:switch wire:model="rss_enabled" label="启用 RSS" description="生成 RSS/Atom 订阅源" />
                    <flux:switch wire:model="maintenance_mode" label="维护模式" description="前台显示维护提示，管理员仍可访问后台" />
                </div>
            </div>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit" wire:confirm="确定保存博客设置？"
                    class="rounded-lg bg-stone-800 px-4 py-2 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300">
                保存设置
            </button>
        </div>
    </form>
</div>
