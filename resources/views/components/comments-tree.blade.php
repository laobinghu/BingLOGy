@props(['comments', 'depth' => 0])

@if ($comments->isNotEmpty())
    <ul class="space-y-4 {{ $depth === 0 ? 'mt-6' : 'mt-3 ml-6 border-l border-stone-200 pl-4 dark:border-stone-700' }}">
        @foreach ($comments as $comment)
            <li class="rounded-lg border border-stone-200 bg-white p-4 dark:border-stone-700 dark:bg-stone-900/50">
                <div class="flex items-center gap-2 text-sm text-stone-500 dark:text-stone-400">
                    <span class="font-medium text-stone-800 dark:text-stone-200">{{ $comment->name }}</span>
                    <span class="text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <div class="mt-2 text-sm leading-6 text-stone-700 dark:text-stone-300 whitespace-pre-line">
                    {{ $comment->body }}
                </div>
                @if ($depth < 1)
                    <details class="mt-3">
                        <summary class="cursor-pointer text-xs text-stone-500 hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-200">回复</summary>
                        <form method="POST" action="{{ route('comments.store', $comment->post_id) }}" class="mt-3 space-y-2">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <input type="text" name="name" placeholder="昵称" required
                                   class="w-full rounded border border-stone-200 px-2 py-1 text-sm dark:border-stone-700 dark:bg-stone-800">
                            <input type="email" name="email" placeholder="邮箱（可选）"
                                   class="w-full rounded border border-stone-200 px-2 py-1 text-sm dark:border-stone-700 dark:bg-stone-800">
                            <textarea name="body" rows="2" required placeholder="说点什么..."
                                      class="w-full rounded border border-stone-200 px-2 py-1 text-sm dark:border-stone-700 dark:bg-stone-800"></textarea>
                            <button type="submit" class="rounded bg-stone-800 px-3 py-1 text-xs text-white hover:bg-stone-700 dark:bg-stone-200 dark:text-stone-800 dark:hover:bg-stone-300">
                                提交回复
                            </button>
                        </form>
                    </details>
                @endif
            </li>
            @if ($comment->children && $comment->children->isNotEmpty() && $depth < 1)
                <x-comments-tree :comments="$comment->children" :depth="$depth + 1" />
            @endif
        @endforeach
    </ul>
@endif
