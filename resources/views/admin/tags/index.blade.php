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

        {{-- Stats cards --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-700 dark:bg-stone-900">
                <p class="text-xs font-medium tracking-wide text-stone-500 uppercase dark:text-stone-400">标签总数</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $totalTags }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-700 dark:bg-stone-900">
                <p class="text-xs font-medium tracking-wide text-stone-500 uppercase dark:text-stone-400">有文章标签</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $activeTags }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-700 dark:bg-stone-900">
                <p class="text-xs font-medium tracking-wide text-stone-500 uppercase dark:text-stone-400">使用率</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $totalTags > 0 ? round($activeTags / $totalTags * 100) : 0 }}%</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-700 dark:bg-stone-900">
                <p class="text-xs font-medium tracking-wide text-stone-500 uppercase dark:text-stone-400">本周热门</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-600 dark:text-amber-400">{{ $trendingTags->count() > 0 ? $trendingTags->first()->name : '—' }}</p>
            </div>
        </div>

        {{-- Search + Create --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <form method="GET" action="{{ route('admin.tags.index') }}" class="flex gap-2">
                    <flux:input
                        name="search"
                        placeholder="搜索标签名称、Slug 或描述..."
                        :value="request('search')"
                        class="flex-1"
                    />
                    @if (request()->has('search'))
                        <a href="{{ route('admin.tags.index') }}" class="inline-flex items-center rounded-lg px-3 text-sm text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200">
                            ✕
                        </a>
                    @endif
                    <flux:button type="submit" variant="primary">搜索</flux:button>
                </form>
            </div>

            <flux:modal.trigger name="create-tag">
                <flux:button variant="primary">+ 新增标签</flux:button>
            </flux:modal.trigger>
        </div>

        {{-- Create Modal --}}
        <flux:modal name="create-tag">
            <flux:heading size="lg">新增标签</flux:heading>
            <form method="POST" action="{{ route('admin.tags.store') }}" class="mt-4 space-y-4">
                @csrf
                <flux:input name="name" label="名称" required />
                <flux:input name="color" label="颜色（可选）" placeholder="#3B82F6" maxlength="7" />
                <flux:input name="sort_order" label="排序（可选）" type="number" min="0" value="0" />
                <flux:select name="parent_id" label="父标签（可选）">
                    <option value="">— 无（顶级标签） —</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </flux:select>
                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">取消</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">创建</flux:button>
                </div>
            </form>
        </flux:modal>

        {{-- Merge Modal --}}
        <flux:modal name="merge-tags">
            <flux:heading size="lg">合并标签</flux:heading>
            <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">
                将源标签的所有文章移动到目标标签，然后删除源标签。
            </p>
            <form method="POST" action="{{ route('admin.tags.merge') }}" class="mt-4 space-y-4">
                @csrf
                <flux:select name="source_id" label="源标签（将被合并并删除）" required>
                    <option value="">-- 请选择 --</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }} ({{ $tag->posts_count }})</option>
                    @endforeach
                </flux:select>
                <flux:select name="target_id" label="目标标签（保留）" required>
                    <option value="">-- 请选择 --</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }} ({{ $tag->posts_count }})</option>
                    @endforeach
                </flux:select>
                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">取消</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger">确认合并</flux:button>
                </div>
            </form>
        </flux:modal>

        {{-- Tag List --}}
        @if ($tags->isEmpty())
            <p class="mt-6 text-neutral-500">
                @if (request()->has('search'))
                    未找到匹配「{{ request('search') }}」的标签。
                @else
                    还没有标签。
                @endif
            </p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-neutral-200 dark:border-neutral-700">
                        <tr>
                            <th class="px-4 py-3 text-sm font-medium w-8">#</th>
                            <th class="px-4 py-3 text-sm font-medium">名称</th>
                            <th class="px-4 py-3 text-sm font-medium">父标签</th>
                            <th class="px-4 py-3 text-sm font-medium">颜色</th>
                            <th class="px-4 py-3 text-sm font-medium">Slug</th>
                            <th class="px-4 py-3 text-sm font-medium">文章数</th>
                            <th class="px-4 py-3 text-sm font-medium">排序</th>
                            <th class="px-4 py-3 text-sm font-medium">描述</th>
                            <th class="px-4 py-3 text-sm font-medium">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tags as $tag)
                            <tr class="border-b border-neutral-200 dark:border-neutral-700 group" x-data="{ editing: false, showDesc: false }">
                                {{-- Row number --}}
                                <td class="px-4 py-2.5 text-xs text-neutral-400">{{ $loop->iteration }}</td>

                                {{-- Name (inline editable) --}}
                                <td class="px-4 py-2.5">
                                    <form
                                        action="{{ route('admin.tags.update', $tag) }}"
                                        method="POST"
                                        x-show="!editing"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <span
                                            class="cursor-pointer rounded px-1.5 py-0.5 text-sm font-medium transition hover:bg-stone-100 dark:hover:bg-stone-800"
                                            @click="editing = true; $nextTick(() => $refs.nameInput.focus())"
                                            title="点击编辑"
                                        >
                                            @if ($tag->parent_id)
                                                <span class="text-stone-400 dark:text-stone-500">└─ </span>
                                            @endif
                                            {{ $tag->name }}
                                        </span>
                                    </form>

                                    <form
                                        action="{{ route('admin.tags.update', $tag) }}"
                                        method="POST"
                                        x-show="editing"
                                        @click.away="editing = false"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <input
                                            type="text"
                                            name="name"
                                            x-ref="nameInput"
                                            value="{{ $tag->name }}"
                                            class="w-28 rounded border border-blue-400 px-1.5 py-0.5 text-sm focus:outline-hidden focus:ring-2 focus:ring-blue-400"
                                            @keydown.escape="editing = false"
                                            @keydown.enter="$el.form.submit()"
                                        />
                                    </form>
                                </td>

                                {{-- Parent --}}
                                <td class="px-4 py-2.5 text-xs text-neutral-500">
                                    @if ($tag->parent)
                                        <a href="{{ route('admin.tags.index', ['search' => $tag->parent->name]) }}" class="hover:underline">
                                            {{ $tag->parent->name }}
                                        </a>
                                    @else
                                        <span class="text-stone-300 dark:text-stone-600">—</span>
                                    @endif
                                </td>

                                {{-- Color --}}
                                <td class="px-4 py-2.5">
                                    <form action="{{ route('admin.tags.update', $tag) }}" method="POST" class="flex items-center gap-1.5">
                                        @csrf
                                        @method('PUT')
                                        @if ($tag->color)
                                            <span class="inline-block h-4 w-4 rounded-full border border-stone-300" style="background-color: {{ $tag->color }}"></span>
                                        @else
                                            <span class="inline-block h-4 w-4 rounded-full border border-dashed border-stone-300"></span>
                                        @endif
                                        <input
                                            type="text"
                                            name="color"
                                            value="{{ $tag->color }}"
                                            placeholder="—"
                                            maxlength="7"
                                            class="w-16 rounded border-0 bg-transparent px-1 py-0.5 text-xs text-stone-500 focus:outline-hidden focus:ring-0"
                                            @change.debounce="$el.form.submit()"
                                        />
                                    </form>
                                </td>

                                {{-- Slug --}}
                                <td class="px-4 py-2.5 text-xs text-neutral-500 font-mono">{{ $tag->slug }}</td>

                                {{-- Posts count --}}
                                <td class="px-4 py-2.5 text-sm tabular-nums">{{ $tag->posts_count }}</td>

                                {{-- Sort order --}}
                                <td class="px-4 py-2.5">
                                    <form action="{{ route('admin.tags.update', $tag) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input
                                            type="number"
                                            name="sort_order"
                                            value="{{ $tag->sort_order }}"
                                            min="0"
                                            class="w-14 rounded border border-neutral-200 px-1.5 py-0.5 text-xs text-center focus:outline-hidden focus:ring-1 focus:ring-blue-400 dark:border-neutral-700"
                                            @change.debounce="$el.form.submit()"
                                        />
                                    </form>
                                </td>

                                {{-- Description (expandable) --}}
                                <td class="px-4 py-2.5">
                                    <button
                                        type="button"
                                        class="text-xs text-stone-400 hover:text-stone-600 dark:hover:text-stone-300"
                                        @click="showDesc = !showDesc"
                                    >
                                        {{ $tag->description ? '📝' : '➕' }}
                                    </button>

                                    <div x-show="showDesc" x-transition @click.away="showDesc = false" class="relative mt-1">
                                        <form action="{{ route('admin.tags.update', $tag) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <textarea
                                                name="description"
                                                rows="2"
                                                class="w-48 rounded border border-stone-200 px-2 py-1 text-xs focus:outline-hidden focus:ring-1 focus:ring-blue-400 dark:border-stone-700 dark:bg-stone-800"
                                                placeholder="标签描述（可选）"
                                            >{{ $tag->description }}</textarea>
                                            <button type="submit" class="mt-0.5 rounded bg-blue-500 px-2 py-0.5 text-[10px] text-white hover:bg-blue-600">保存</button>
                                        </form>
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-2.5 space-x-2 whitespace-nowrap">
                                    <flux:modal.trigger name="edit-tag-{{ $tag->id }}">
                                        <button type="button" class="text-xs text-blue-600 hover:underline dark:text-blue-400">编辑</button>
                                    </flux:modal.trigger>

                                    <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="inline"
                                          onsubmit="return confirm('确定删除标签「{{ $tag->name }}」？该操作将从所有文章中移除该标签。')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline dark:text-red-400">删除</button>
                                    </form>

                                    {{-- Edit Detail Modal --}}
                                    <flux:modal name="edit-tag-{{ $tag->id }}">
                                        <flux:heading size="lg">编辑标签「{{ $tag->name }}」</flux:heading>
                                        <form action="{{ route('admin.tags.update', $tag) }}" method="POST" class="mt-4 space-y-4">
                                            @csrf
                                            @method('PUT')
                                            <flux:input name="name" label="名称" :value="$tag->name" required />
                                            <flux:input name="slug" label="Slug" :value="$tag->slug" hint="修改 slug 可能影响已有链接" />
                                            <flux:input name="color" label="颜色" :value="$tag->color" placeholder="#3B82F6" maxlength="7" />
                                            <flux:textarea name="description" label="描述" rows="3">{{ $tag->description }}</flux:textarea>
                                            <flux:input name="sort_order" label="排序" type="number" min="0" :value="$tag->sort_order" />
                                            <flux:select name="parent_id" label="父标签">
                                                <option value="">— 无（顶级标签） —</option>
                                                @foreach ($parents as $parent)
                                                    <option value="{{ $parent->id }}" @selected($tag->parent_id === $parent->id)>{{ $parent->name }}</option>
                                                @endforeach
                                            </flux:select>
                                            <div class="flex justify-end gap-2">
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">取消</flux:button>
                                                </flux:modal.close>
                                                <flux:button type="submit" variant="primary">保存</flux:button>
                                            </div>
                                        </form>
                                    </flux:modal>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-neutral-500 tabular-nums">
                    共 {{ $tags->count() }} 个标签
                </p>

                <flux:modal.trigger name="merge-tags">
                    <flux:button variant="ghost" size="sm">合并标签</flux:button>
                </flux:modal.trigger>
            </div>
        @endif
    </div>
</x-layouts::app>
