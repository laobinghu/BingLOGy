@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-5xl px-6 py-14 lg:py-20">
        <section class="border-b border-stone-300/70 pb-12 dark:border-stone-700/60">
            <p class="text-sm font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">
                个人博客
            </p>
            <h1 class="mt-4 max-w-3xl text-4xl leading-tight font-semibold tracking-tight text-stone-950 sm:text-5xl dark:text-stone-50">
                记录建造、写作，以及那些还在进行中的事。
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-700 dark:text-stone-300">
                一个安静的地方，放文章、开发笔记、未完成的想法，以及那些慢慢变得有用的零碎经验。
            </p>
        </section>

        @if ($featured)
            <section class="mt-14">
                @include('components.post-card-featured', ['post' => $featured])
            </section>
        @endif

        <section class="mt-16">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">
                        最近归档
                    </p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-stone-950 dark:text-stone-50">
                        近期更新
                    </h2>
                </div>

                <a
                    href="{{ route('posts.index') }}"
                    class="shrink-0 text-sm text-stone-600 transition hover:text-stone-950 dark:text-stone-400 dark:hover:text-stone-100"
                >
                    查看全部归档 →
                </a>
            </div>

            <div class="mt-8">
                @if ($posts->isNotEmpty())
                    @include('components.post-timeline', ['groups' => \App\Support\PostPresenter::groupByYear($posts)])
                @elseif (! $featured)
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
                @else
                    <p class="text-sm text-stone-500 dark:text-stone-400">
                        目前只有最新的一篇，更多文章陆续整理中。
                    </p>
                @endif
            </div>
        </section>
    </div>
@endsection
