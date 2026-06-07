<x-layouts::app :title="__('文章管理')">
  <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('success'))
      <flux:callout color="green" icon="check-circle">{{ session('success') }}</flux:callout>
    @endif
    @if (session('error'))
      <flux:callout color="red" icon="x-circle">{{ session('error') }}</flux:callout>
    @endif

    @php
      $_filterStatus = $status ?? 'all';
      $_allVariant = $_filterStatus === 'all' ? 'filled' : 'ghost';
      $_publishedVariant = $_filterStatus === 'published' ? 'filled' : 'ghost';
      $_draftVariant = $_filterStatus === 'draft' ? 'filled' : 'ghost';
      $_createUrl = route('admin.posts.create');
      $_allUrl = route('admin.posts.index');
      $_publishedUrl = route('admin.posts.index', ['status' => 'published']);
      $_draftUrl = route('admin.posts.index', ['status' => 'draft']);
    @endphp
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
      <flux:heading size="lg">文章管理</flux:heading>

      <div class="flex flex-wrap items-center gap-2">
        <form
          method="POST"
          action="{{ route('admin.posts.bulk') }}"
          id="bulk-form"
          class="flex items-center gap-2"
        >
          @csrf

          <flux:select id="bulk-select" placeholder="批量操作..." class="w-40">
            <option value="publish">发布选中</option>
            <option value="unpublish">撤销发布</option>
            <option value="tags_append">标签：追加</option>
            <option value="tags_replace">标签：替换</option>
            <option value="tags_remove">标签：移除</option>
            <option value="delete">删除选中</option>
          </flux:select>

          <flux:input
            type="text"
            name="tags"
            id="bulk-tags"
            placeholder="标签名，逗号分隔"
            class="hidden w-48"
          />

          <flux:button type="button" onclick="BulkManager.submit()">
            执行
          </flux:button>
        </form>

        <flux:button variant="primary" href="{{ $_createUrl }}">
          + 新建文章
        </flux:button>
      </div>
    </div>

    <div class="flex items-center gap-1 rounded-xl border border-neutral-200 p-1 dark:border-neutral-700">
      <flux:button href="{{ $_allUrl }}" size="sm" variant="{{ $_allVariant }}">
        全部
      </flux:button>
      <flux:button href="{{ $_publishedUrl }}" size="sm" variant="{{ $_publishedVariant }}">
        已发布
      </flux:button>
      <flux:button href="{{ $_draftUrl }}" size="sm" variant="{{ $_draftVariant }}">
        草稿/定时
      </flux:button>
    </div>

    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
      @if ($posts->isEmpty())
        <div class="flex items-center justify-center h-full text-neutral-500">
          还没有文章，点上面按钮写第一篇吧！
        </div>
      @else
        {{-- Desktop table --}}
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-neutral-200 dark:border-neutral-700">
              <tr>
                <th class="w-10 px-4 py-3 font-medium">
                  <flux:checkbox id="bulk-check-all" />
                </th>
                <th class="px-4 py-3 font-medium">标题</th>
                <th class="px-4 py-3 font-medium">状态</th>
                <th class="px-4 py-3 font-medium">发布时间</th>
                <th class="px-4 py-3 font-medium">标签</th>
                <th class="px-4 py-3 font-medium">操作</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($posts as $post)
                @php
                  $_isPublished = $post->published_at && $post->published_at->isPast();
                  $_editUrl = route('admin.posts.edit', $post);
                  $_previewUrl = route('posts.show', $post);
                @endphp
                <tr class="border-b border-neutral-200 dark:border-neutral-700">
                  <td class="px-4 py-3">
                    <flux:checkbox class="bulk-item" value="{{ $post->id }}" />
                  </td>
                  <td class="px-4 py-3 font-medium">{{ $post->title }}</td>
                  <td class="px-4 py-3">
                    @if ($_isPublished)
                      <flux:badge color="emerald" size="sm">已发布</flux:badge>
                    @elseif ($post->published_at && $post->published_at->isFuture())
                      <flux:badge color="yellow" size="sm">定时</flux:badge>
                    @else
                      <flux:badge color="zinc" size="sm">草稿</flux:badge>
                    @endif
                  </td>
                  <td class="px-4 py-3 text-neutral-500">
                    {{ $post->published_at ? $post->published_at->format('Y-m-d H:i') : '-' }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-1">
                      @foreach ($post->tags as $tag)
                        <flux:badge color="zinc" size="sm">{{ $tag->name }}</flux:badge>
                      @endforeach
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                      <flux:button variant="ghost" size="sm" href="{{ $_editUrl }}">
                        <span class="text-blue-600 hover:text-blue-700 dark:text-blue-400">编辑</span>
                      </flux:button>

                      @unless ($_isPublished)
                        <form
                          action="{{ route('admin.posts.bulk') }}"
                          method="POST"
                          class="inline"
                          onsubmit="return confirm('此操作会将文章标记为已发布，继续？')"
                        >
                          @csrf
                          <input type="hidden" name="action" value="publish">
                          <input type="hidden" name="ids" value="{{ $post->id }}">
                          <flux:button variant="ghost" size="sm" type="submit">
                            <span class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">发布</span>
                          </flux:button>
                        </form>
                      @endunless

                      @if ($_isPublished)
                        <form
                          action="{{ route('admin.posts.bulk') }}"
                          method="POST"
                          class="inline"
                          onsubmit="return confirm('确定撤销发布？')"
                        >
                          @csrf
                          <input type="hidden" name="action" value="unpublish">
                          <input type="hidden" name="ids" value="{{ $post->id }}">
                          <flux:button variant="ghost" size="sm" type="submit">
                            <span class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400">撤销</span>
                          </flux:button>
                        </form>
                      @endif

                      <flux:button variant="ghost" size="sm" href="{{ $_previewUrl }}" target="_blank" rel="noopener">
                        <span class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400">预览</span>
                      </flux:button>

                      <form
                        action="{{ route('admin.posts.destroy', $post) }}"
                        method="POST"
                        class="inline"
                        onsubmit="return confirm('确定删除？')"
                      >
                        @csrf
                        @method('DELETE')
                        <flux:button variant="ghost" size="sm" type="submit">
                          <span class="text-red-600 hover:text-red-700 dark:text-red-400">删除</span>
                        </flux:button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Mobile cards --}}
        <div class="lg:hidden grid gap-4 p-4">
          @foreach ($posts as $post)
            @php
              $_isPublished = $post->published_at && $post->published_at->isPast();
              $_editUrl = route('admin.posts.edit', $post);
              $_previewUrl = route('posts.show', $post);
            @endphp
            <flux:card class="p-4">
              <div class="flex items-start gap-3">
                <flux:checkbox class="bulk-item" value="{{ $post->id }}" />
                <div class="flex-1 min-w-0 space-y-1.5">
                  <div class="font-medium truncate">{{ $post->title }}</div>

                  <div>
                    @if ($_isPublished)
                      <flux:badge color="emerald" size="sm">已发布</flux:badge>
                    @elseif ($post->published_at && $post->published_at->isFuture())
                      <flux:badge color="yellow" size="sm">定时</flux:badge>
                    @else
                      <flux:badge color="zinc" size="sm">草稿</flux:badge>
                    @endif
                  </div>

                  <div class="text-sm text-neutral-500">
                    {{ $post->published_at ? $post->published_at->format('Y-m-d H:i') : '-' }}
                  </div>

                  @if ($post->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-1">
                      @foreach ($post->tags as $tag)
                        <flux:badge color="zinc" size="sm">{{ $tag->name }}</flux:badge>
                      @endforeach
                    </div>
                  @endif

                  <div class="flex flex-wrap items-center gap-1 pt-1">
                    <flux:button variant="ghost" size="sm" href="{{ $_editUrl }}">
                      <span class="text-blue-600 hover:text-blue-700 dark:text-blue-400">编辑</span>
                    </flux:button>

                    @unless ($_isPublished)
                      <form
                        action="{{ route('admin.posts.bulk') }}"
                        method="POST"
                        class="inline"
                        onsubmit="return confirm('此操作会将文章标记为已发布，继续？')"
                      >
                        @csrf
                        <input type="hidden" name="action" value="publish">
                        <input type="hidden" name="ids" value="{{ $post->id }}">
                        <flux:button variant="ghost" size="sm" type="submit">
                          <span class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">发布</span>
                        </flux:button>
                      </form>
                    @endunless

                    @if ($_isPublished)
                      <form
                        action="{{ route('admin.posts.bulk') }}"
                        method="POST"
                        class="inline"
                        onsubmit="return confirm('确定撤销发布？')"
                      >
                        @csrf
                        <input type="hidden" name="action" value="unpublish">
                        <input type="hidden" name="ids" value="{{ $post->id }}">
                        <flux:button variant="ghost" size="sm" type="submit">
                          <span class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400">撤销</span>
                        </flux:button>
                      </form>
                    @endif

                    <flux:button variant="ghost" size="sm" href="{{ $_previewUrl }}" target="_blank" rel="noopener">
                      <span class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400">预览</span>
                    </flux:button>

                    <form
                      action="{{ route('admin.posts.destroy', $post) }}"
                      method="POST"
                      class="inline"
                      onsubmit="return confirm('确定删除？')"
                    >
                      @csrf
                      @method('DELETE')
                      <flux:button variant="ghost" size="sm" type="submit">
                        <span class="text-red-600 hover:text-red-700 dark:text-red-400">删除</span>
                      </flux:button>
                    </form>
                  </div>
                </div>
              </div>
            </flux:card>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  <script>
    const BulkManager = {
      submit() {
        const select = document.getElementById('bulk-select');
        const action = select.value;
        const tagsInput = document.getElementById('bulk-tags');
        const checked = document.querySelectorAll('ui-checkbox.bulk-item[data-checked]');

        if (!action) {
          alert('请先选择批量操作。');
          return false;
        }

        const needsTags = ['tags_append', 'tags_replace', 'tags_remove'].includes(action);
        if (needsTags) {
          if (!tagsInput.value.trim()) {
            alert('标签操作需要填写标签名（逗号分隔）。');
            tagsInput.focus();
            return false;
          }
        } else {
          tagsInput.value = '';
        }

        if (!checked.length) {
          alert('请先勾选至少一篇文章。');
          return false;
        }

        if (action === 'delete') {
          if (!confirm('确定删除选中的 ' + checked.length + ' 篇文章？')) {
            return false;
          }
        }

        checked.forEach(el => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'ids[]';
          input.value = el.getAttribute('value');
          document.getElementById('bulk-form').appendChild(input);
        });

        document.getElementById('bulk-form').submit();
        return true;
      },
    };

    document.getElementById('bulk-select')?.addEventListener('change', function () {
      const tagsInput = document.getElementById('bulk-tags');
      const needsTags = ['tags_append', 'tags_replace', 'tags_remove'].includes(this.value);
      tagsInput.classList.toggle('hidden', !needsTags);
    });

    document.getElementById('bulk-check-all')?.addEventListener('click', function () {
      const isChecked = !this.hasAttribute('data-checked');
      document.querySelectorAll('ui-checkbox.bulk-item').forEach(el => {
        if (isChecked !== el.hasAttribute('data-checked')) {
          el.click();
        }
      });
    });
  </script>
</x-layouts::app>
