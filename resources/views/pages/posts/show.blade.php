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

        <div class="mt-10">
            <div class="prose prose-stone dark:prose-invert max-w-none">
                {!! \App\Services\HookManager::applyFilters('post.content.render', \App\Support\PostPresenter::bodyHtml($post), $post) !!}
            </div>
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

    <section class="mx-auto max-w-3xl px-6 pb-16">
        <h2 class="text-lg font-semibold tracking-tight text-stone-950 dark:text-stone-50">
            评论 ({{ $comments->count() }})
        </h2>

        @if (session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('comments.store', $post) }}" class="mt-4 space-y-3 rounded-lg border border-stone-200 bg-white p-4 dark:border-stone-700 dark:bg-stone-900/50">
            @csrf
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <input type="text" name="name" placeholder="昵称" required
                       class="rounded border border-stone-200 px-3 py-2 text-sm dark:border-stone-700 dark:bg-stone-800">
                <input type="email" name="email" placeholder="邮箱（可选）"
                       class="rounded border border-stone-200 px-3 py-2 text-sm dark:border-stone-700 dark:bg-stone-800">
            </div>
            <textarea name="body" rows="3" required placeholder="说点什么..."
                      class="w-full rounded border border-stone-200 px-3 py-2 text-sm dark:border-stone-700 dark:bg-stone-800"></textarea>
            <button type="submit" class="rounded bg-stone-800 px-4 py-2 text-sm text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300">
                提交评论
            </button>
        </form>

        <x-comments-tree :comments="$comments" :depth="0" />
    </section>
@endsection
