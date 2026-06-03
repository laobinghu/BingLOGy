<header class="border-b border-stone-300/70 dark:border-stone-700/60">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
        <a href="{{ route('home') }}" class="text-xl font-semibold tracking-tight text-stone-950 dark:text-stone-50">
            {{ config('app.name', 'BingLOGy') }}
        </a>

        <nav class="flex items-center gap-5 text-sm text-stone-700 dark:text-stone-300">
            <a href="{{ route('home') }}" class="transition hover:text-stone-950 dark:hover:text-stone-50 {{ request()->routeIs('home') ? 'text-stone-950 dark:text-stone-50' : '' }}">
                首页
            </a>
            <a href="{{ route('posts.index') }}" class="transition hover:text-stone-950 dark:hover:text-stone-50 {{ request()->routeIs('posts.*') ? 'text-stone-950 dark:text-stone-50' : '' }}">
                归档
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="transition hover:text-stone-950 dark:hover:text-stone-50">
                    仪表盘
                </a>
            @else
                <a href="{{ route('login') }}" class="transition hover:text-stone-950 dark:hover:text-stone-50">
                    登录
                </a>
            @endauth

            <button
                type="button"
                id="theme-toggle"
                aria-label="切换深浅色"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-stone-300/70 text-stone-600 transition hover:border-stone-400 hover:text-stone-900 dark:border-stone-700 dark:text-stone-400 dark:hover:border-stone-500 dark:hover:text-stone-100"
            >
                <span data-icon="light" hidden>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4">
                        <circle cx="12" cy="12" r="4" />
                        <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                    </svg>
                </span>
                <span data-icon="dark" hidden>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                </span>
                <span data-icon="system" hidden>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4">
                        <rect x="3" y="4" width="18" height="12" rx="2" />
                        <path stroke-linecap="round" d="M8 20h8M12 16v4" />
                    </svg>
                </span>
            </button>
        </nav>
    </div>
</header>
