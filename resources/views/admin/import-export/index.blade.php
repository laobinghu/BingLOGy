<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('success'))
        <div class="whitespace-pre-line rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-2 flex items-center justify-between">
        <h2 class="text-xl font-semibold">导入 / 导出</h2>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button wire:click="setTab('paste')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'paste' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            粘贴 Markdown
        </button>
        <button wire:click="setTab('upload')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'upload' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            上传 .md / .zip
        </button>
        <button wire:click="setTab('export')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'export' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            批量导出
        </button>
    </div>

    @if ($tab === 'paste')
        <div class="space-y-3 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <p class="text-sm text-stone-500 dark:text-stone-400">把多篇带 YAML front matter 的 Markdown 一并粘贴进来，可自动拆分为多篇文章。</p>
            <textarea
                wire:model.live.debounce.500ms="rawPaste"
                rows="10"
                placeholder="---\ntitle: 第一篇\nslug: first-post\ndate: 2026-06-07 12:00\ntags: [php, laravel]\n---\n\n正文 1...\n\n---\ntitle: 第二篇\nslug: second\n---"
                class="w-full rounded-lg border border-neutral-200 px-3 py-2 font-mono text-xs dark:border-neutral-700 dark:bg-stone-900"
            ></textarea>
        </div>
    @elseif ($tab === 'upload')
        <form wire:submit="parseUpload" class="space-y-3 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <p class="text-sm text-stone-500 dark:text-stone-400">支持上传多个 <code>.md</code> 文件，或一个包含 <code>.md</code> 的 zip 压缩包。</p>
            <input
                type="file"
                wire:model="uploadedFiles"
                multiple
                accept=".md,.markdown,.zip,text/markdown,application/zip"
                class="block w-full text-sm"
            >
            @error('uploadedFiles.*') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">解析</button>
                <label class="flex items-center gap-2 text-xs">
                    <input type="checkbox" wire:model="publishOnImport">
                    <span>导入时直接发布</span>
                </label>
            </div>
        </form>
    @elseif ($tab === 'export')
        <div class="space-y-3 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <p class="text-sm text-stone-500 dark:text-stone-400">点击下面按钮下载所选文章的 Markdown 包（zip）。</p>
            <a href="{{ route('admin.import-export.export-all', ['all' => 1]) }}"
               class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">
                下载全部 ({{ $exportable->count() }}) 篇
            </a>
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 dark:border-neutral-700">
                        <tr>
                            <th class="px-3 py-2">标题</th>
                            <th class="px-3 py-2">Slug</th>
                            <th class="px-3 py-2">更新时间</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($exportable as $post)
                        <tr class="border-b border-neutral-100 dark:border-neutral-800">
                            <td class="px-3 py-2">{{ $post->title }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $post->slug }}</td>
                            <td class="px-3 py-2 text-xs text-stone-500">{{ $post->updated_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-center text-stone-500">暂无文章</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (! empty($parsed))
        <div class="space-y-3 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">解析结果（{{ count($parsed) }} 条）</h3>
                <div class="flex items-center gap-2 text-sm">
                    <label class="flex items-center gap-1.5">
                        <input type="checkbox"
                               @checked(count($selected) === count(array_filter($parsed, fn ($r) => empty($r['errors']))))
                               wire:change="toggleAll($event.target.checked)">
                        <span>全选可导入项</span>
                    </label>
                    <label class="flex items-center gap-1.5">
                        <input type="checkbox" wire:model="publishOnImport">
                        <span>导入时直接发布</span>
                    </label>
                    <button wire:click="importSelected"
                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-white hover:bg-blue-700">
                        导入所选
                    </button>
                </div>
            </div>

            <div class="space-y-2">
                @foreach ($parsed as $i => $row)
                    <div class="rounded-lg border p-3 text-sm {{ empty($row['errors']) ? 'border-stone-200 dark:border-stone-700' : 'border-red-300 bg-red-50/40 dark:border-red-800 dark:bg-red-900/20' }}">
                        <div class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                wire:model="selected"
                                value="{{ $i }}"
                                @disabled(! empty($row['errors']))
                                class="mt-1"
                            >
                            <div class="flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold">{{ $row['title'] ?: '(无标题)' }}</span>
                                    <span class="text-xs text-stone-500">来源：{{ $row['source'] }}</span>
                                    @if (! empty($row['date']))
                                        <span class="text-xs text-stone-500">· {{ $row['date'] }}</span>
                                    @endif
                                </div>
                @php $rowTags = $selectedTags[$i] ?? ($row['tags'] ?? []); @endphp
                @if (! empty($rowTags))
                  <div class="flex flex-wrap gap-1">
                    @foreach ($rowTags as $tag)
                      @php $checked = in_array($tag, $rowTags, true) ? 'checked' : ''; @endphp
                      <label class="flex cursor-pointer items-center gap-1 rounded-full border border-neutral-200 px-2 py-0.5 text-[11px] dark:border-neutral-700">
                        <input
                          type="checkbox"
                          value="{{ $tag }}"
                          wire:model="selectedTags.{{ $i }}"
                          {{ $checked }}
                        >
                        <span>{{ $tag }}</span>
                      </label>
                    @endforeach
                  </div>
                @endif

                <div class="flex items-center gap-1">
                  <input
                    type="text"
                    placeholder="新增标签，回车确认..."
                    class="w-40 rounded border border-neutral-200 px-2 py-1 text-xs dark:border-neutral-700 dark:bg-stone-900"
                    wire:keydown.enter.prevent="$wire.addTagToRow({{ $i }}, $event.target.value); $event.target.value = ''"
                  >
                </div>
                                @if (! empty($row['excerpt']))
                                    <p class="text-xs text-stone-500">摘要：{{ $row['excerpt'] }}</p>
                                @endif
                                @if (! empty($row['preview']))
                                    <p class="line-clamp-2 text-xs text-stone-500">{{ $row['preview'] }}</p>
                                @endif
                                @if (! empty($row['errors']))
                                    <ul class="text-xs text-red-600">
                                        @foreach ($row['errors'] as $err)
                                            <li>· {{ $err }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
