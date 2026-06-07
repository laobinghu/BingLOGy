<x-layouts::app :title="__('编辑文章')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <flux:heading size="lg">编辑文章</flux:heading>

            @php
            $_previewUrl = route('posts.show', $post);
            $_exportUrl = route('admin.posts.export', $post);
        @endphp
        <div class="flex items-center gap-2">
                <flux:button variant="ghost" size="sm" href="{{ $_previewUrl }}" target="_blank">
                    预览
                </flux:button>

                <flux:button variant="filled" size="sm" href="{{ $_exportUrl }}">
                    下载 .md
                </flux:button>
            </div>
        </div>

        @include('admin.posts._form', ['editing' => true, 'post' => $post])
    </div>
</x-layouts::app>
