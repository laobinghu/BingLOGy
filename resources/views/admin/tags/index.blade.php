<x-layouts::app :title="__('标签管理')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-xl font-semibold">标签管理</h2>
        </div>

        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <form method="POST" action="{{ route('admin.tags.store') }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1">新增标签</label>
                    <input type="text" name="name" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" placeholder="输入标签名称" required>
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">添加</button>
            </form>
        </div>

        @if ($tags->isEmpty())
            <p class="text-neutral-500">还没有标签。</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-neutral-200 dark:border-neutral-700">
                        <tr>
                            <th class="px-4 py-3 text-sm font-medium">名称</th>
                            <th class="px-4 py-3 text-sm font-medium">Slug</th>
                            <th class="px-4 py-3 text-sm font-medium">文章数</th>
                            <th class="px-4 py-3 text-sm font-medium">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tags as $tag)
                            <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                <td class="px-4 py-3">{{ $tag->name }}</td>
                                <td class="px-4 py-3 text-sm text-neutral-500">{{ $tag->slug }}</td>
                                <td class="px-4 py-3 text-sm">{{ $tag->posts_count }}</td>
                                <td class="px-4 py-3 space-x-2">
                                    <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="inline"
                                          onsubmit="return confirm('确定删除标签「{{ $tag->name }}」？')">
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
</x-layouts::app>
