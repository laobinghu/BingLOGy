@extends('layouts.public', ['title' => $tag->name])

@push('meta')
    <meta name="description" content="{{ $tag->description ? strip_tags($tag->description) : $tag->name . ' - 鍏?' . $posts->total() . ' 绡囨枃绔? }}">
    <link rel="canonical" href="{{ route('tags.show', $tag->slug) }}">
    <meta property="og:title" content="{{ $tag->name }}">
    <meta property="og:description" content="{{ $tag->description ? strip_tags($tag->description) : $tag->name . ' - 鍏?' . $posts->total() . ' 绡囨枃绔? }}">
    <meta property="og:url" content="{{ route('tags.show', $tag->slug) }}">
    <meta property="og:type" content="website">
    @if ($tag->color)
        <meta name="theme-color" content="{{ $tag->color }}">
    @endif
@endpush

@section('content')
    <div class="mx-auto max-w-5xl px-6 py-14 lg:py-20">
        <header class="border-b border-stone-300/70 pb-10 dark:border-stone-700/60">
            @if ($tag->color)
                <span class="inline-block size-3 rounded-full align-middle" style="background-color: {{ $tag->color }}"></span>
            @endif
            <span class="ml-1.5 text-sm font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">鏍囩</span>

            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl dark:text-stone-50">
                {{ $tag->name }}
            </h1>

            @if ($tag->description)
                <p class="mt-3 text-base text-stone-600 dark:text-stone-400">
                    {{ $tag->description }}
                </p>
            @endif

            @if ($childrenTags->isNotEmpty())
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium tracking-wide text-stone-400 uppercase dark:text-stone-500">瀛愭爣绛撅細</span>
                    @foreach ($childrenTags as $child)
                        <a
                            href="{{ route('tags.show', $child->slug) }}"
                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium
                                bg-stone-200/60 text-stone-700 hover:bg-stone-300/60 hover:text-stone-900
                                dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-stone-700 dark:hover:text-stone-200"
                        >
                            @if ($child->color)
                                <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $child->color }}"></span>
                            @endif
                            {{ $child->name }}
                            <span class="tabular-nums text-stone-400">({{ $child->posts_count }})</span>
                        </a>
                    @endforeach
                </div>
            @elseif ($tag->parent)
                <div class="mt-4">
                    <a
                        href="{{ route('tags.show', $tag->parent->slug) }}"
                        class="inline-flex items-center gap-1 text-xs font-medium text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200"
                    >
                        鈫?杩斿洖銆寋{ $tag->parent->name }}銆?
                    </a>
                </div>
            @endif

            @if ($relatedTags->isNotEmpty())
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium tracking-wide text-stone-400 uppercase dark:text-stone-500">甯镐笌姝ゆ爣绛句竴璧蜂娇鐢細</span>
                    @foreach ($relatedTags as $related)
                        <a
                            href="{{ route('tags.show', $related->slug) }}"
                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium
                                bg-blue-50 text-blue-700 hover:bg-blue-100
                                dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                        >
                            @if ($related->color)
                                <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $related->color }}"></span>
                            @endif
                            {{ $related->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">
                鍏?{{ $posts->total() }} 绡?
                @if ($tag->meta['seo_description'] ?? false)
                    路 {{ $tag->meta['seo_description'] }}
                @endif
            </p>
        </header>

        <div class="mt-10">
            @if ($posts->isNotEmpty())
                @include('components.post-timeline', [
                    'groups' => \App\Support\PostPresenter::groupByYear($posts->getCollection()),
                ])

                @if ($posts->hasPages())
                    <div class="mt-12 border-t border-stone-300/70 pt-6 text-sm dark:border-stone-700/60">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <p class="text-stone-500 dark:text-stone-400 tabular-nums">
                                绗?{{ $posts->firstItem() }}鈥搟{ $posts->lastItem() }} 绡?/ 鍏?{{ $posts->total() }} 绡?
                            </p>

                            <div class="flex items-center gap-1">
                                @if ($posts->onFirstPage())
                                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-full text-stone-300 dark:text-stone-600">&lsaquo;</span>
                                @else
                                    <a href="{{ $posts->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-stone-700 transition hover:bg-stone-200/60 hover:text-stone-950 dark:text-stone-300 dark:hover:bg-stone-800 dark:hover:text-stone-50">&lsaquo;</a>
                                @endif

                                @foreach ($posts->getUrlRange(max(1, $posts->currentPage() - 2), min($posts->lastPage(), $posts->currentPage() + 2)) as $page => $url)
                                    @if ($page == $posts->currentPage())
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-stone-900 text-xs font-medium text-stone-50 dark:bg-stone-100 dark:text-stone-900">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-xs text-stone-700 transition hover:bg-stone-200/60 hover:text-stone-950 dark:text-stone-300 dark:hover:bg-stone-800 dark:hover:text-stone-50">{{ $page }}</a>
                                    @endif
                                @endforeach

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
                        杩樻病鏈夋枃绔?
                    </p>
                    <p class="mt-3 text-base text-stone-700 dark:text-stone-300">
                        璇ユ爣绛句笅杩樻病鏈夊凡鍙戝竷鐨勬枃绔犮€?
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
