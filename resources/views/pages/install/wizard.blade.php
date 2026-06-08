<x-layouts::install :title="__('安装 BingLOGy')">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid w-full gap-8 xl:grid-cols-[0.9fr_1.1fr]">
            <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-8 shadow-[0_24px_80px_rgba(0,0,0,0.08)] backdrop-blur dark:border-stone-800 dark:bg-stone-900/85">
                <div class="mb-8">
                    <p class="mb-3 inline-flex rounded-full border border-stone-300 px-3 py-1 text-xs font-medium uppercase tracking-[0.28em] text-stone-500 dark:border-stone-700 dark:text-stone-400">
                        BingLOGy Installer
                    </p>
                    <h1 class="text-3xl font-semibold tracking-tight text-stone-950 dark:text-white">多页安装向导</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-stone-600 dark:text-stone-400">
                        按步骤完成环境检查、数据库配置、站点信息和管理员创建。
                    </p>
                </div>

                @if (!empty($errorsList))
                    <div class="mb-6 space-y-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                        @foreach ($errorsList as $message)
                            <p>{{ $message }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-3">
                    @php
                        $order = ['intro', 'database', 'site', 'admin', 'review'];
                    @endphp
                    @foreach ($order as $key)
                        <div class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm {{ $step === $key ? 'border-stone-900 bg-stone-900 text-white dark:border-stone-100 dark:bg-stone-100 dark:text-stone-900' : 'border-stone-200 bg-stone-50 text-stone-700 dark:border-stone-800 dark:bg-stone-950/60 dark:text-stone-300' }}">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] opacity-70">{{ str_pad((string) (array_search($key, $order) + 1), 2, '0', STR_PAD_LEFT) }}</p>
                                <p class="mt-1 font-semibold">{{ $steps[$key] ?? ucfirst($key) }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $step === $key ? 'bg-white/15 text-current dark:bg-black/10' : 'bg-white text-stone-500 dark:bg-stone-900 dark:text-stone-400' }}">
                                {{ $step === $key ? '当前步骤' : '步骤' }}
                            </span>
                        </div>
                    @endforeach
                </div>

                @if ($step === 'intro')
                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-950/60">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div class="rounded-xl bg-white px-3 py-2 text-sm dark:bg-stone-900">PHP: {{ $status['checks']['runtime']['php_version']['value'] ?? PHP_VERSION }}</div>
                                <div class="rounded-xl bg-white px-3 py-2 text-sm dark:bg-stone-900">APP_KEY: {{ $status['checks']['runtime']['app_key']['value'] ?? 'missing' }}</div>
                                <div class="rounded-xl bg-white px-3 py-2 text-sm dark:bg-stone-900">DB: {{ $status['checks']['runtime']['database_driver']['value'] ?? config('database.default') }}</div>
                                <div class="rounded-xl bg-white px-3 py-2 text-sm dark:bg-stone-900">Install: {{ ($status['installed'] ?? false) ? 'installed' : 'not installed' }}</div>
                            </div>
                        </div>

                        @if (!($status['ready'] ?? false))
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                                当前环境还未完全就绪。请检查 PHP 版本、必要扩展和目录写入权限后继续。
                            </div>
                        @endif

                        <a href="{{ route('install.database') }}" class="inline-flex items-center justify-center rounded-2xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 dark:bg-stone-100 dark:text-stone-900">
                            开始安装
                        </a>
                    </div>
                @elseif ($step === 'database')
                    <form method="POST" action="{{ route('install.database.store') }}" class="mt-6 space-y-5">
                        @csrf
                        <input type="hidden" name="install_token" value="{{ $installToken }}">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">数据库类型</span>
                                <select name="db_connection" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950">
                                    <option value="sqlite" @selected(($form['db_connection'] ?? 'sqlite') === 'sqlite')>sqlite</option>
                                    <option value="mysql" @selected(($form['db_connection'] ?? '') === 'mysql')>mysql</option>
                                    <option value="mariadb" @selected(($form['db_connection'] ?? '') === 'mariadb')>mariadb</option>
                                    <option value="pgsql" @selected(($form['db_connection'] ?? '') === 'pgsql')>pgsql</option>
                                    <option value="sqlsrv" @selected(($form['db_connection'] ?? '') === 'sqlsrv')>sqlsrv</option>
                                </select>
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">数据库名或路径</span>
                                <input name="db_database" value="{{ $form['db_database'] ?? 'database/database.sqlite' }}" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">数据库主机</span>
                                <input name="db_host" value="{{ $form['db_host'] ?? '127.0.0.1' }}" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">数据库端口</span>
                                <input name="db_port" value="{{ $form['db_port'] ?? '3306' }}" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">数据库用户名</span>
                                <input name="db_username" value="{{ $form['db_username'] ?? '' }}" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">数据库密码</span>
                                <input type="password" name="db_password" value="{{ $form['db_password'] ?? '' }}" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('install.index') }}" class="text-sm text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">返回</a>
                            <button type="submit" class="rounded-2xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white dark:bg-stone-100 dark:text-stone-900">下一步</button>
                        </div>
                    </form>
                @elseif ($step === 'site')
                    <form method="POST" action="{{ route('install.site.store') }}" class="mt-6 space-y-5">
                        @csrf
                        <input type="hidden" name="install_token" value="{{ $installToken }}">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">站点名称</span>
                                <input name="app_name" value="{{ $form['app_name'] ?? 'BingLOGy' }}" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">站点地址</span>
                                <input name="app_url" value="{{ $form['app_url'] ?? 'http://localhost' }}" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">时区</span>
                                <input name="app_timezone" value="{{ $form['app_timezone'] ?? 'Asia/Shanghai' }}" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('install.database') }}" class="text-sm text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">上一步</a>
                            <button type="submit" class="rounded-2xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white dark:bg-stone-100 dark:text-stone-900">下一步</button>
                        </div>
                    </form>
                @elseif ($step === 'admin')
                    <form method="POST" action="{{ route('install.admin.store') }}" class="mt-6 space-y-5">
                        @csrf
                        <input type="hidden" name="install_token" value="{{ $installToken }}">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">管理员姓名</span>
                                <input name="admin_name" value="{{ $form['admin_name'] ?? '' }}" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">管理员邮箱</span>
                                <input type="email" name="admin_email" value="{{ $form['admin_email'] ?? '' }}" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                            <label class="grid gap-2 sm:col-span-2">
                                <span class="text-sm font-medium text-stone-700 dark:text-stone-300">管理员密码</span>
                                <input type="password" name="admin_password" value="{{ $form['admin_password'] ?? '' }}" required class="rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm dark:border-stone-700 dark:bg-stone-950" />
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('install.site') }}" class="text-sm text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">上一步</a>
                            <button type="submit" class="rounded-2xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white dark:bg-stone-100 dark:text-stone-900">下一步</button>
                        </div>
                    </form>
                @elseif ($step === 'review')
                    <div class="mt-6 space-y-5">
                        <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm dark:border-stone-800 dark:bg-stone-950/60">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div>站点：{{ $form['app_name'] ?? 'BingLOGy' }}</div>
                                <div>地址：{{ $form['app_url'] ?? 'http://localhost' }}</div>
                                <div>数据库：{{ $form['db_connection'] ?? 'sqlite' }}</div>
                                <div>管理员：{{ $form['admin_email'] ?? 'unknown' }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('install.finish') }}">
                            @csrf
                            <input type="hidden" name="install_token" value="{{ $installToken }}">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('install.admin') }}" class="text-sm text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">上一步</a>
                                <button type="submit" class="rounded-2xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white dark:bg-stone-100 dark:text-stone-900">开始安装</button>
                            </div>
                        </form>
                    </div>
                @endif
            </section>
        </div>
    </main>
</x-layouts::install>
