@extends('layouts.public', ['title' => $post->title])

@section('content')
    <article class="mx-auto max-w-3xl px-6 py-14 lg:py-20">
        <nav class="mb-10 text-sm">
            <a href="{{ route('posts.index') }}" class="text-stone-500 transition hover:text-stone-900 dark:text-stone-400 dark:hover:text-stone-100">
                ← 返回归档
            </a>
        </nav>

        @if ($post->cover_image)
            <div class="mb-10 overflow-hidden rounded-[2rem]">
                <img src="{{ Storage::url($post->cover_image) }}" alt="{{ $post->title }}" class="h-64 w-full object-cover sm:h-80">
            </div>
        @endif

        <header class="border-b border-stone-300/70 pb-8 dark:border-stone-700/60">
            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-stone-500 dark:text-stone-400">
                <time datetime="{{ $post->published_at->toDateString() }}" class="tabular-nums">
                    {{ $post->published_at->format('Y 年 n 月 j 日') }}
                </time>
                <span class="text-stone-300 dark:text-stone-600">·</span>
                <span class="tabular-nums">{{ \App\Support\PostPresenter::readingTime($post) }} 分钟阅读</span>
                <span class="text-stone-300 dark:text-stone-600">·</span>
                <span class="tabular-nums">{{ \App\Support\PostPresenter::wordCount($post) }} 字</span>
                <span class="text-stone-300 dark:text-stone-600">·</span>
                <span class="tabular-nums">{{ number_format($post->views) }} 次阅读</span>
            </p>

            <h1 class="mt-4 text-3xl leading-tight font-semibold tracking-tight text-stone-950 sm:text-4xl dark:text-stone-50">
                {{ $post->title }}
            </h1>

            @if ($post->excerpt)
                <p class="mt-5 text-lg leading-8 text-stone-600 dark:text-stone-400">
                    {{ $post->excerpt }}
                </p>
            @endif

            @if ($post->tags->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-1.5">
                    @foreach ($post->tags as $tag)
                        <a href="{{ route('posts.index', ['tag' => $tag->slug]) }}"
                           class="rounded-full bg-stone-200/60 px-2.5 py-0.5 text-[11px] font-medium text-stone-600 transition hover:bg-stone-300/60 dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-stone-700">
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        <div class="prose prose-stone dark:prose-invert mt-10 max-w-none">
            {!! \App\Support\PostPresenter::bodyHtml($post) !!}
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
