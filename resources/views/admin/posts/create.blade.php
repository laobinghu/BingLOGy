<x-layouts::app :title="__('新建文章')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-semibold">新建文章</h2>
            <details class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm dark:border-neutral-700 dark:bg-stone-900">
                <summary class="cursor-pointer select-none font-medium">从 Markdown 粘贴</summary>
                <form method="POST" action="{{ route('admin.import-export.preview') }}" class="mt-3 space-y-2">
                    @csrf
                    <textarea name="raw" rows="8" placeholder="---\ntitle: ...\n---\n正文..." class="w-full rounded border border-neutral-200 px-2 py-1 font-mono text-xs dark:border-neutral-700 dark:bg-stone-800"></textarea>
                    <button type="submit" class="rounded bg-blue-600 px-3 py-1 text-white hover:bg-blue-700">解析并填充</button>
                </form>
            </details>
        </div>

        <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">标题</label>
                <input type="text" name="title" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('title') }}" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Slug（可选，自动生成）</label>
                <input type="text" name="slug" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('slug') }}">
                @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                <x-markdown-editor name="body" :value="old('body')" />
                <p class="mt-1 text-xs text-neutral-500">支持 Markdown / GFM 语法。可包含 <code>[[_TOC_]]</code> 占位符自动生成目录。</p>
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">标签（逗号分隔，可在 front matter 中用 <code>tags</code>）</label>
                <input type="text" name="tags_csv" class="w-full rounded-lg border border-neutral-200 px-3 py-2 dark:border-neutral-700" value="{{ old('tags_csv') }}" placeholder="php, laravel, livewire">
                <p class="mt-1 text-xs text-neutral-500">或从下方勾选现有标签（两者合并）。</p>
            </div>

            @if (($tags ?? collect())->isNotEmpty())
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">现有标签</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                    @checked(in_array($tag->id, old('tags', [])))>
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

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
