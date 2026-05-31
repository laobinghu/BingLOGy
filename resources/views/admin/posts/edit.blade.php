<x-layouts::app :title="__('编辑文章')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <form method="POST" action="{{ route('admin.posts.update', $post) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">标题</label>
                <input type="text" name="title" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('title', $post->title) }}" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">摘要（可选）</label>
                <textarea name="excerpt" rows="2" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">正文</label>
                <textarea name="body" rows="12" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" required>{{ old('body', $post->body) }}</textarea>
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">发布时间</label>
                <input type="datetime-local" name="published_at" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700"
                       value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
            </div>

            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                保存
            </button>
        </form>
    </div>
</x-layouts::app>
