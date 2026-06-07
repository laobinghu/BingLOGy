@props([
    'post' => null,
    'editing' => false,
])

@php
    if ($editing) {
        $currentTags = $post->tags->pluck('name')->all();
        $coverImage = $post->cover_image;
    } else {
        $currentTags = [];
        $coverImage = null;
    }
    $publishStatus = old('publish_status', $editing ? $publishStatus : 'draft');
    $tagsCsvValue = old('tags_csv', $editing ? implode(', ', $currentTags) : '');
@endphp

<form
    method="POST"
    action="{{ $editing ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card class="p-4">
                <flux:input
                    name="title"
                    label="标题"
                    :value="old('title', $editing ? $post->title : '')"
                    required
                />
            </flux:card>

            <flux:card class="p-4">
                <x-markdown-editor name="body" :value="old('body', $editing ? $post->body : '')" />
                @error('body')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </flux:card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Slug / Excerpt / Status --}}
            <flux:card class="p-4 space-y-3">
                <flux:input
                    name="slug"
                    label="Slug"
                    :value="old('slug', $editing ? $post->slug : '')"
                    hint="留空自动生成"
                />

                <flux:textarea
                    name="excerpt"
                    label="摘要"
                    :value="old('excerpt', $editing ? $post->excerpt : '')"
                    rows="2"
                />

                <div x-data="{ showScheduled: @js($publishStatus === 'scheduled') }">
                    <flux:select
                        name="publish_status"
                        label="状态"
                        x-init="$el.value = @js($publishStatus)"
                        x-on:change="showScheduled = $el.value === 'scheduled'"
                    >
                        <option value="draft">草稿</option>
                        <option value="published">已发布</option>
                        <option value="scheduled">定时发布</option>
                    </flux:select>

                    <div x-show="showScheduled" x-transition class="mt-3">
                        <flux:input
                            type="datetime-local"
                            name="published_at"
                            label="发布时间"
                            :value="old('published_at', $editing && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '')"
                        />
                    </div>
                </div>
            </flux:card>

            {{-- Tags --}}
            <flux:card class="p-4 space-y-3">
                <div
                    x-data="{
                        tagsCsv: @js($tagsCsvValue),
                        selectedTags: [],
                        searchQuery: '',

                        init() {
                            this.selectedTags = this.tagsCsv
                                ? this.tagsCsv.split(',').map(s => s.trim()).filter(Boolean)
                                : [];
                        },

                        toggleTag(name) {
                            const idx = this.selectedTags.indexOf(name);
                            if (idx === -1) {
                                this.selectedTags.push(name);
                            } else {
                                this.selectedTags.splice(idx, 1);
                            }
                        },

                        confirmTags() {
                            this.tagsCsv = this.selectedTags.join(', ');
                            this.$refs.tagsInput.value = this.tagsCsv;
                        },

                        isSelected(name) {
                            return this.selectedTags.includes(name);
                        },
                    }"
                >
                    <flux:input
                        name="tags_csv"
                        label="标签"
                        placeholder="php, laravel, livewire"
                        x-ref="tagsInput"
                        x-model="tagsCsv"
                    />
                    @error('tags_csv')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    @if (($tags ?? collect())->isNotEmpty())
                        <flux:modal.trigger name="tag-picker">
                            <flux:button variant="ghost" size="sm" class="mt-2">
                                从已有标签选择
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:modal name="tag-picker">
                            <flux:heading size="lg">选择标签</flux:heading>

                            <flux:input
                                placeholder="搜索标签..."
                                class="mt-4"
                                x-on:input="searchQuery = $event.target.value"
                            />

                            <div class="mt-4 space-y-1 max-h-80 overflow-y-auto">
                                @foreach ($tags as $tag)
                                    <label
                                        class="flex items-center gap-2 text-sm cursor-pointer px-2 py-1.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-white/5"
                                        x-show="!searchQuery || @js($tag->name).toLowerCase().includes(searchQuery.toLowerCase())"
                                    >
                                        <input
                                            type="checkbox"
                                            class="rounded border-zinc-300 dark:border-zinc-600"
                                            :checked="isSelected(@js($tag->name))"
                                            x-on:change="toggleTag(@js($tag->name))"
                                        />
                                        <span>{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <flux:modal.close>
                                    <flux:button variant="ghost">取消</flux:button>
                                </flux:modal.close>
                                <flux:button variant="primary" x-on:click="confirmTags()">确认</flux:button>
                            </div>
                        </flux:modal>
                    @endif
                </div>
            </flux:card>

            {{-- Cover image --}}
            <flux:card class="p-4 space-y-3">
                <div
                    x-data="{
                        preview: null,
                        hasExisting: @js($editing && (bool) $coverImage),
                        dragging: false,

                        previewFile(event) {
                            const file = event.target.files[0];
                            if (file) this.preview = URL.createObjectURL(file);
                        },

                        handleDrop(event) {
                            this.dragging = false;
                            const file = event.dataTransfer.files[0];
                            if (file) {
                                this.$refs.fileInput.files = event.dataTransfer.files;
                                this.preview = URL.createObjectURL(file);
                            }
                        },

                        clearPreview() {
                            this.preview = null;
                            this.$refs.fileInput.value = '';
                        },
                    }"
                >
                    {{-- Drop zone --}}
                    <div
                        x-show="!preview && !hasExisting"
                        class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-xl p-8 text-center cursor-pointer transition-colors"
                        :class="dragging ? '!border-blue-500 !bg-blue-50 dark:!bg-blue-900/20' : ''"
                        x-on:click="$refs.fileInput.click()"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="handleDrop($event)"
                    >
                        <flux:icon.cloud-arrow-up class="mx-auto size-8 text-zinc-400" />
                        <p class="mt-2 text-sm text-zinc-500">拖拽图片到此处，或点击上传</p>
                        <p class="text-xs text-zinc-400 mt-1">JPEG / PNG / WebP，最大 2MB</p>
                    </div>

                    <input
                        type="file"
                        name="cover_image"
                        accept="image/jpeg,image/png,image/webp"
                        x-ref="fileInput"
                        class="hidden"
                        x-on:change="previewFile($event)"
                    />

                    {{-- New image preview --}}
                    <div
                        x-show="preview"
                        class="relative rounded-xl overflow-hidden border"
                    >
                        <img :src="preview" class="h-40 w-full object-cover" />
                        <button
                            type="button"
                            class="absolute top-2 right-2 bg-white/80 dark:bg-zinc-800/80 rounded-full p-1 hover:bg-white dark:hover:bg-zinc-800 transition-colors"
                            x-on:click="clearPreview()"
                        >
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    </div>

                    {{-- Existing cover image (edit mode) --}}
                    @if ($editing && $coverImage)
                        <div x-show="!preview && hasExisting" class="mt-3 space-y-2">
                            <img
                                src="{{ Storage::url($coverImage) }}"
                                class="h-32 w-full object-cover rounded-lg border"
                            />
                            <flux:checkbox name="remove_cover" value="1" label="删除当前封面" />
                        </div>
                    @endif

                    @error('cover_image')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </flux:card>
        </div>
    </div>

    {{-- Submit bar --}}
    @php $_indexUrl = route('admin.posts.index'); @endphp
    <div class="flex justify-end gap-2">
        <flux:button href="{{ $_indexUrl }}" variant="ghost">取消</flux:button>
        <flux:button type="submit" variant="primary">
            {{ $editing ? '保存' : '创建文章' }}
        </flux:button>
    </div>
</form>
