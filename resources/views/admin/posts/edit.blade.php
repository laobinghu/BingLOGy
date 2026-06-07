<x-layouts::app :title="__('编辑文章')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-semibold">编辑文章</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('posts.show', $post) }}" class="rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm hover:bg-neutral-50 dark:border-neutral-700 dark:bg-stone-900" target="_blank">预览</a>
                <a href="{{ route('admin.posts.export', $post) }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm text-white hover:bg-emerald-700">下载 .md</a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">标题</label>
                <input type="text" name="title" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('title', $post->title) }}" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Slug（可选）</label>
                <input type="text" name="slug" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('slug', $post->slug) }}">
                @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">摘要（可选）</label>
                <textarea name="excerpt" rows="2" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">封面图（可选）</label>
                @if ($post->cover_image)
                    <div class="mb-2 overflow-hidden rounded-lg border border-neutral-200 dark:border-neutral-700">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($post->cover_image) }}" alt="当前封面图" class="h-40 w-full object-cover">
                    </div>
                    <label class="flex items-center gap-2 text-sm mb-2">
                        <input type="checkbox" name="remove_cover" value="1">
                        <span class="text-red-600">删除当前封面</span>
                    </label>
                @endif
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm dark:border-neutral-700">
                @error('cover_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">正文</label>
                <x-markdown-editor name="body" :value="old('body', $post->body)" />
                <p class="mt-1 text-xs text-neutral-500">支持 Markdown / GFM 语法。可包含 <code>[[_TOC_]]</code> 占位符自动生成目录。</p>
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">标签（逗号分隔）</label>
                @php
                    $currentTags = $post->tags->pluck('name')->all();
                    $currentTagsCsv = old('tags_csv', implode(', ', $currentTags));
                @endphp
                <input type="text" name="tags_csv" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ $currentTagsCsv }}" placeholder="php, laravel, livewire">
                <p class="mt-1 text-xs text-neutral-500">会替换下方现有勾选结果。</p>
            </div>

            @if (($tags ?? collect())->isNotEmpty())
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">现有标签</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                    @checked(in_array($tag->id, old('tags', $post->tags->pluck('id')->all())))>
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">状态</label>
                <div class="space-y-2">
                    <p class="text-xs text-neutral-500">
                        当前状态：
                        @if ($post->published_at && $post->published_at->isPast())
                            <span class="text-green-600 font-medium">已发布</span>
                            （{{ $post->published_at->format('Y-m-d H:i') }}）
                        @elseif ($post->published_at && $post->published_at->isFuture())
                            <span class="text-yellow-600 font-medium">定时发布</span>
                            （{{ $post->published_at->format('Y-m-d H:i') }}）
                        @else
                            <span class="text-yellow-600 font-medium">草稿</span>
                        @endif
                    </p>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="publish" value="1"
                            @checked($post->published_at && $post->published_at->isPast())>
                        <span>已发布</span>
                    </label>

                    <p class="text-xs text-neutral-500">
                        或指定定时发布时间：
                        <input type="datetime-local" name="published_at"
                            class="mt-1 rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700"
                            value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                    </p>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                保存
            </button>
        </form>
    </div>
</x-layouts::app>
