@props(['groups'])

<div class="space-y-12">
    @foreach ($groups as $group)
        <section>
            <div class="flex items-baseline gap-4">
                <h3 class="text-sm font-semibold tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">
                    {{ $group['year'] }}
                </h3>
                <div class="h-px flex-1 bg-stone-300/60 dark:bg-stone-700/60"></div>
                <span class="text-xs text-stone-400 dark:text-stone-500">
                    {{ $group['posts']->count() }} 篇
                </span>
            </div>

            <ol class="mt-6 border-l border-stone-300/70 pl-6 dark:border-stone-700/60">
                @foreach ($group['posts'] as $post)
                    <li class="group relative pb-6 last:pb-0">
                        <span class="absolute -left-[27px] top-2 h-2 w-2 rounded-full bg-stone-300 ring-4 ring-paper transition group-hover:bg-stone-500 dark:bg-stone-600 dark:ring-stone-950 dark:group-hover:bg-stone-400"></span>

                        <a href="{{ route('posts.show', $post) }}" class="block transition">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:gap-6">
                                <time
                                    datetime="{{ $post->published_at->toDateString() }}"
                                    class="w-28 shrink-0 text-xs font-medium tracking-wider text-stone-500 uppercase tabular-nums dark:text-stone-400"
                                >
                                    {{ $post->published_at->format('M j') }}
                                </time>
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        @if ($post->cover_image)
                                            <img src="{{ Storage::url($post->cover_image) }}" alt="" class="mt-0.5 h-10 w-16 shrink-0 rounded-lg object-cover">
                                        @endif
                                        <h4 class="text-base font-semibold text-stone-900 group-hover:text-stone-700 dark:text-stone-100 dark:group-hover:text-stone-300">
                                            {{ $post->title }}
                                        </h4>
                                    </div>
                                    <p class="mt-1 line-clamp-1 text-sm text-stone-600 dark:text-stone-400">
                                        {{ \App\Support\PostPresenter::excerpt($post, 120) }}
                                    </p>
                                    @if ($post->relationLoaded('tags') && $post->tags->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                            @foreach ($post->tags as $tag)
                                                <span class="rounded-full bg-stone-200/50 px-2 py-0.5 text-[10px] font-medium text-stone-600 dark:bg-stone-800 dark:text-stone-400">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right text-xs text-stone-400 tabular-nums dark:text-stone-500">
                                    <div>{{ \App\Support\PostPresenter::readingTime($post) }} 分钟</div>
                                    @if ($post->views)
                                        <div>{{ number_format($post->views) }} 次阅读</div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ol>
        </section>
    @endforeach
</div>
