<x-layouts::app :title="__('仪表盘')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- 统计卡片 --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative flex flex-col justify-center overflow-hidden rounded-xl border border-neutral-200 px-6 py-5 dark:border-neutral-700">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">文章总数</p>
                <p class="mt-1 text-3xl font-bold">{{ $totalPosts }}</p>
            </div>
            <div class="relative flex flex-col justify-center overflow-hidden rounded-xl border border-neutral-200 px-6 py-5 dark:border-neutral-700">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">已发布</p>
                <p class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400">{{ $publishedPosts }}</p>
            </div>
            <div class="relative flex flex-col justify-center overflow-hidden rounded-xl border border-neutral-200 px-6 py-5 dark:border-neutral-700">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">草稿</p>
                <p class="mt-1 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $draftPosts }}</p>
            </div>
        </div>

        {{-- 近期文章 --}}
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold">近期文章</h3>

                @if ($recentPosts->isEmpty())
                    <p class="text-neutral-500">还没有文章，去写第一篇吧！</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b border-neutral-200 dark:border-neutral-700">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-medium">标题</th>
                                    <th class="px-4 py-3 text-sm font-medium">状态</th>
                                    <th class="px-4 py-3 text-sm font-medium">发布时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPosts as $post)
                                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.posts.edit', $post) }}"
                                               class="text-blue-600 hover:underline dark:text-blue-400">
                                                {{ $post->title }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($post->published_at && $post->published_at->isPast())
                                                <span class="text-green-600 dark:text-green-400">已发布</span>
                                            @else
                                                <span class="text-yellow-600 dark:text-yellow-400">草稿</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">
                                            {{ $post->published_at ? $post->published_at->format('Y-m-d H:i') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
