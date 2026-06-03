@extends('layouts.public', ['title' => '归档'])

@section('content')
    <div class="mx-auto max-w-5xl px-6 py-14 lg:py-20">
        <header class="border-b border-stone-300/70 pb-10 dark:border-stone-700/60">
            <p class="text-sm font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">
                归档
            </p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl dark:text-stone-50">
                所有文章
            </h1>
            <p class="mt-3 text-base text-stone-600 dark:text-stone-400">
                共 {{ $posts->total() }} 篇，按发布时间倒序。
            </p>
        </header>

        <div class="mt-12">
            @if ($posts->isNotEmpty())
                @include('partials.post-timeline', [
                    'groups' => \App\Support\PostPresenter::groupByYear($posts->getCollection()),
                ])

                @if ($posts->hasMorePages())
                    <div class="mt-12 flex items-center justify-between border-t border-stone-300/70 pt-6 text-sm dark:border-stone-700/60">
                        <p class="text-stone-500 dark:text-stone-400">
                            第 {{ $posts->firstItem() }}–{{ $posts->lastItem() }} 篇 / 共 {{ $posts->total() }} 篇
                        </p>
                        <div class="flex items-center gap-4">
                            @if ($posts->onFirstPage())
                                <span class="cursor-not-allowed text-stone-300 dark:text-stone-600">← 上一页</span>
                            @else
                                <a href="{{ $posts->previousPageUrl() }}" class="text-stone-700 transition hover:text-stone-950 dark:text-stone-300 dark:hover:text-stone-50">← 上一页</a>
                            @endif

                            @if ($posts->hasMorePages())
                                <a href="{{ $posts->nextPageUrl() }}" class="text-stone-700 transition hover:text-stone-950 dark:text-stone-300 dark:hover:text-stone-50">下一页 →</a>
                            @else
                                <span class="cursor-not-allowed text-stone-300 dark:text-stone-600">下一页 →</span>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="rounded-[2rem] border border-dashed border-stone-300/80 bg-paper-soft p-10 text-center dark:border-stone-700/60 dark:bg-stone-900/40">
                    <p class="text-sm font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">
                        还没有文章
                    </p>
                    <p class="mt-3 text-base text-stone-700 dark:text-stone-300">
                        等第一篇文章发布后，会出现在这里。
                    </p>
                    @auth
                        <a
                            href="{{ route('admin.posts.create') }}"
                            class="mt-6 inline-flex items-center rounded-full bg-stone-900 px-5 py-2.5 text-sm font-medium text-stone-50 transition hover:bg-stone-700 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-300"
                        >
                            去写第一篇
                        </a>
                    @endauth
                </div>
            @endif
        </div>
    </div>
@endsection
