<x-layouts::app :title="__('文章管理')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-xl font-semibold">文章管理</h2>
            <a href="{{ route('admin.posts.create') }}"
               class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                + 新建文章
            </a>
        </div>

        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            @php
                $posts = \App\Models\Post::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
            @endphp

            @if ($posts->isEmpty())
                <div class="flex items-center justify-center h-full text-neutral-500">
                    还没有文章，点上面按钮写第一篇吧！
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-neutral-200 dark:border-neutral-700">
                        <tr>
                            <th class="px-4 py-3 text-sm font-medium">标题</th>
                            <th class="px-4 py-3 text-sm font-medium">状态</th>
                            <th class="px-4 py-3 text-sm font-medium">发布时间</th>
                            <th class="px-4 py-3 text-sm font-medium">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($posts as $post)
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-3">{{ $post->title }}</td>
                                <td class="px-4 py-3">
                                    @if ($post->published_at && $post->published_at->isPast())
                                        <span class="text-green-600 dark:text-green-400">已发布</span>
                                    @else
                                        <span class="text-yellow-600 dark:text-yellow-400">草稿</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ $post->published_at ? $post->published_at->format('Y-m-d H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 space-x-2">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-600 hover:underline dark:text-blue-400">编辑</a>
                                    <a href="{{ route('posts.show', $post) }}" class="text-gray-600 hover:underline dark:text-gray-400" target="_blank">预览</a>
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline"
                                          onsubmit="return confirm('确定删除？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline dark:text-red-400">删除</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
