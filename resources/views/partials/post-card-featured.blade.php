@props(['post'])

<article class="rounded-[2rem] border border-stone-300/80 bg-paper-soft p-8 dark:border-stone-700/60 dark:bg-stone-900/60 sm:p-10">
    <p class="text-xs font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">
        最新
    </p>

    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl dark:text-stone-50">
        <a href="{{ route('posts.show', $post) }}" class="transition hover:text-stone-700 dark:hover:text-stone-300">
            {{ $post->title }}
        </a>
    </h2>

    <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-stone-500 dark:text-stone-400">
        <time datetime="{{ $post->published_at->toDateString() }}" class="tabular-nums">
            {{ $post->published_at->format('Y 年 n 月 j 日') }}
        </time>
        <span class="text-stone-300 dark:text-stone-600">·</span>
        <span class="tabular-nums">{{ \App\Support\PostPresenter::readingTime($post) }} 分钟阅读</span>
        <span class="text-stone-300 dark:text-stone-600">·</span>
        <span class="tabular-nums">{{ \App\Support\PostPresenter::wordCount($post) }} 字</span>
    </p>

    <p class="mt-6 max-w-2xl text-base leading-7 text-stone-700 sm:text-lg sm:leading-8 dark:text-stone-300">
        {{ \App\Support\PostPresenter::excerpt($post, 240) }}
    </p>

    <a
        href="{{ route('posts.show', $post) }}"
        class="mt-7 inline-flex items-center gap-1.5 text-sm font-medium text-stone-900 underline decoration-stone-400 underline-offset-4 transition hover:decoration-stone-700 dark:text-stone-100 dark:decoration-stone-600 dark:hover:decoration-stone-300"
    >
        阅读全文
        <span aria-hidden="true">→</span>
    </a>
</article>
