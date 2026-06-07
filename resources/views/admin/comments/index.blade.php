<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-2 flex items-center justify-between">
        <h2 class="text-xl font-semibold">评论管理</h2>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button wire:click="setTab('all')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'all' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            全部 ({{ $counts['all'] }})
        </button>
        <button wire:click="setTab('pending')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'pending' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            待审核 ({{ $counts['pending'] }})
        </button>
        <button wire:click="setTab('approved')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'approved' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            已批准 ({{ $counts['approved'] }})
        </button>
        <button wire:click="setTab('spam')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $tab === 'spam' ? 'border-stone-800 bg-stone-800 text-white dark:border-stone-200 dark:bg-stone-200 dark:text-stone-800' : 'border-stone-200 text-stone-600 dark:border-stone-700 dark:text-stone-400' }}">
            垃圾 ({{ $counts['spam'] }})
        </button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
        <table class="w-full text-left">
            <thead class="border-b border-neutral-200 dark:border-neutral-700">
                <tr>
                    <th class="px-4 py-3 text-sm font-medium">访客</th>
                    <th class="px-4 py-3 text-sm font-medium">文章</th>
                    <th class="px-4 py-3 text-sm font-medium">内容</th>
                    <th class="px-4 py-3 text-sm font-medium">时间</th>
                    <th class="px-4 py-3 text-sm font-medium">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comments as $c)
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $c->name }}</div>
                            @if ($c->email)
                                <div class="text-xs text-stone-500">{{ $c->email }}</div>
                            @endif
                            <div class="text-xs text-stone-400">{{ \Illuminate\Support\Str::limit($c->ip_address, 20) }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($c->post)
                                <a href="{{ route('admin.posts.edit', $c->post) }}" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ \Illuminate\Support\Str::limit($c->post->title, 30) }}
                                </a>
                            @else
                                <span class="text-stone-400">已删除</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">{{ \Illuminate\Support\Str::limit($c->body, 80) }}</td>
                        <td class="px-4 py-3 text-xs text-stone-500">{{ $c->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if ($c->status !== 'approved')
                                    <button wire:click="approve({{ $c->id }})"
                                            class="text-xs text-green-600 hover:underline dark:text-green-400">
                                        批准
                                    </button>
                                @endif
                                @if ($c->status !== 'spam')
                                    <span class="text-stone-300 dark:text-stone-600">·</span>
                                    <button wire:click="markSpam({{ $c->id }})"
                                            class="text-xs text-yellow-600 hover:underline dark:text-yellow-400">
                                        垃圾
                                    </button>
                                @endif
                                <span class="text-stone-300 dark:text-stone-600">·</span>
                                <button wire:click="delete({{ $c->id }})"
                                        wire:confirm="确定删除此评论？"
                                        class="text-xs text-red-600 hover:underline dark:text-red-400">
                                    删除
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-stone-500">没有评论。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
