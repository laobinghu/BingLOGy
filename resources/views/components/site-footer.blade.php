@php
    $siteName = \App\Services\SettingsManager::siteName();
@endphp

<footer class="mt-20 border-t border-stone-300/70 pt-8 text-sm text-stone-500 dark:border-stone-700/60 dark:text-stone-400">
    <div class="mx-auto flex max-w-5xl flex-col items-start justify-between gap-3 px-6 pb-12 sm:flex-row sm:items-center">
        <p>
            &copy; {{ date('Y') }} {{ $siteName }}
        </p>
        <p class="flex items-center gap-4">
            <a href="{{ route('feed') }}" class="transition hover:text-stone-900 dark:hover:text-stone-100">
                RSS
            </a>
            <span class="text-stone-300 dark:text-stone-600">·</span>
            <span>Powered by {{ \App\Services\SettingsManager::siteName() }}</span>
        </p>
    </div>
</footer>

