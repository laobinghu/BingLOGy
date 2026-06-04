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

        @if ($allTags->isNotEmpty())
            <div class="mt-8 mb-12 flex flex-wrap items-center gap-2">
                <span class="text-xs font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">标签</span>
                <a href="{{ route('posts.index') }}"
                    class="rounded-full px-3 py-1 text-xs font-medium transition {{ request()->missing('tag') ? 'bg-stone-900 text-stone-50 dark:bg-stone-100 dark:text-stone-900' : 'bg-stone-200/60 text-stone-700 hover:bg-stone-300/60 dark:bg-stone-800 dark:text-stone-300 dark:hover:bg-stone-700' }}">
                    全部
                </a>
                @foreach ($allTags as $tag)
                    <a href="{{ route('posts.index', ['tag' => $tag->slug]) }}"
                        class="rounded-full px-3 py-1 text-xs font-medium transition {{ request('tag') === $tag->slug ? 'bg-stone-900 text-stone-50 dark:bg-stone-100 dark:text-stone-900' : 'bg-stone-200/60 text-stone-700 hover:bg-stone-300/60 dark:bg-stone-800 dark:text-stone-300 dark:hover:bg-stone-700' }}">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            @if ($posts->isNotEmpty())
                @include('partials.post-timeline', [
                    'groups' => \App\Support\PostPresenter::groupByYear($posts->getCollection()),
                ])

                @if ($posts->hasPages())
                    <div class="mt-12 border-t border-stone-300/70 pt-6 text-sm dark:border-stone-700/60">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <p class="text-stone-500 dark:text-stone-400 tabular-nums">
                                第 {{ $posts->firstItem() }}–{{ $posts->lastItem() }} 篇 / 共 {{ $posts->total() }} 篇
                            </p>

                            <div class="flex items-center gap-1">
                                {{-- Previous --}}
                                @if ($posts->onFirstPage())
                                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-full text-stone-300 dark:text-stone-600">&lsaquo;</span>
                                @else
                                    <a href="{{ $posts->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-stone-700 transition hover:bg-stone-200/60 hover:text-stone-950 dark:text-stone-300 dark:hover:bg-stone-800 dark:hover:text-stone-50">&lsaquo;</a>
                                @endif

                                {{-- Page numbers --}}
                                @foreach ($posts->getUrlRange(max(1, $posts->currentPage() - 2), min($posts->lastPage(), $posts->currentPage() + 2)) as $page => $url)
                                    @if ($page == $posts->currentPage())
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-stone-900 text-xs font-medium text-stone-50 dark:bg-stone-100 dark:text-stone-900">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-xs text-stone-700 transition hover:bg-stone-200/60 hover:text-stone-950 dark:text-stone-300 dark:hover:bg-stone-800 dark:hover:text-stone-50">{{ $page }}</a>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if ($posts->hasMorePages())
                                    <a href="{{ $posts->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-stone-700 transition hover:bg-stone-200/60 hover:text-stone-950 dark:text-stone-300 dark:hover:bg-stone-800 dark:hover:text-stone-50">&rsaquo;</a>
                                @else
                                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-full text-stone-300 dark:text-stone-600">&rsaquo;</span>
                                @endif
                            </div>
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
