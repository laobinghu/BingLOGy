<x-layouts::app :title="__('新建文章')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">标题</label>
                <input type="text" name="title" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('title') }}" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">摘要（可选）</label>
                <textarea name="excerpt" rows="2" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">{{ old('excerpt') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">封面图（可选）</label>
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm dark:border-neutral-700">
                @error('cover_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">正文</label>
                <textarea name="body" rows="12" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" required>{{ old('body') }}</textarea>
                <p class="mt-1 text-xs text-neutral-500">支持 Markdown 语法。</p>
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">标签</label>
                <div class="flex flex-wrap gap-2">
                    @forelse ($tags as $tag)
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                @checked(in_array($tag->id, old('tags', [])))>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-neutral-500">暂无标签，在标签管理中添加。</p>
                    @endforelse
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">状态</label>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="publish" value="1" @checked(old('publish'))>
                        <span>立即发布</span>
                    </label>
                    <p class="text-xs text-neutral-500">
                        或指定定时发布时间：
                        <input type="datetime-local" name="published_at" class="mt-1 rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('published_at') }}">
                    </p>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                创建文章
            </button>
        </form>
    </div>
</x-layouts::app>
