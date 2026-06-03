@extends('layouts.public', ['title' => $post->title])

@section('content')
    <article class="mx-auto max-w-3xl px-6 py-14 lg:py-20">
        <nav class="mb-10 text-sm">
            <a href="{{ route('posts.index') }}" class="text-stone-500 transition hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">
                ← 返回归档
            </a>
        </nav>

        <header class="border-b border-stone-300/70 pb-8 dark:border-stone-700/60">
            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-stone-500 dark:text-stone-400">
                <time datetime="{{ $post->published_at->toDateString() }}" class="tabular-nums">
                    {{ $post->published_at->format('Y 年 n 月 j 日') }}
                </time>
                <span class="text-stone-300 dark:text-stone-600">·</span>
                <span class="tabular-nums">{{ \App\Support\PostPresenter::readingTime($post) }} 分钟阅读</span>
                <span class="text-stone-300 dark:text-stone-600">·</span>
                <span class="tabular-nums">{{ \App\Support\PostPresenter::wordCount($post) }} 字</span>
            </p>

            <h1 class="mt-4 text-3xl leading-tight font-semibold tracking-tight text-stone-950 sm:text-4xl dark:text-stone-50">
                {{ $post->title }}
            </h1>

            @if ($post->excerpt)
                <p class="mt-5 text-lg leading-8 text-stone-600 dark:text-stone-400">
                    {{ $post->excerpt }}
                </p>
            @endif
        </header>

        <div class="mt-10 max-w-none text-base leading-8 text-stone-800 dark:text-stone-200">
            {!! nl2br(e($post->body)) !!}
        </div>

        <footer class="mt-16 border-t border-stone-300/70 pt-8 text-sm dark:border-stone-700/60">
            <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('posts.index') }}" class="text-stone-600 transition hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">
                    ← 回到归档
                </a>
                <a href="{{ route('feed') }}" class="text-stone-500 transition hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">
                    订阅 RSS
                </a>
            </div>
        </footer>
    </article>
@endsection
