<x-layouts::install :title="__('安装完成')">
    <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <section class="w-full rounded-3xl border border-stone-200/80 bg-white/90 p-8 shadow-[0_24px_80px_rgba(0,0,0,0.08)] backdrop-blur dark:border-stone-800 dark:bg-stone-900/90">
            <div class="mb-6">
                <p class="mb-3 inline-flex rounded-full border border-stone-300 px-3 py-1 text-xs font-medium uppercase tracking-[0.28em] text-stone-500 dark:border-stone-700 dark:text-stone-400">
                    BingLOGy Installer
                </p>
                <h1 class="text-3xl font-semibold tracking-tight text-stone-950 dark:text-white">安装完成</h1>
                <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-400">
                    系统已完成初始化，安装入口已锁定。你现在可以前往登录页开始使用博客。
                </p>
            </div>

            <div class="grid gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700 dark:border-stone-800 dark:bg-stone-950/60 dark:text-stone-300">
                <div class="flex items-center justify-between">
                    <span>安装时间</span>
                    <span>{{ $payload['installed_at'] ?? now()->toDateTimeString() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>应用名称</span>
                    <span>{{ $payload['app_name'] ?? config('app.name') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>管理员邮箱</span>
                    <span>{{ $payload['payload']['admin_email'] ?? 'unknown' }}</span>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-stone-500 dark:text-stone-400">
                    若要重新安装，需要先删除安装锁文件。
                </p>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-2xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 dark:bg-stone-100 dark:text-stone-900">
                    前往登录
                </a>
            </div>
        </section>
    </main>
</x-layouts::install>
