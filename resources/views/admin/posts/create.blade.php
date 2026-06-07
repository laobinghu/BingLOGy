<x-layouts::app :title="__('新建文章')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('success'))
            <flux:callout color="green" icon="check-circle">{{ session('success') }}</flux:callout>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-2">
            <flux:heading size="lg">新建文章</flux:heading>

            @include('admin.posts._markdown-import')
        </div>

        @include('admin.posts._form', ['editing' => false, 'post' => null])
    </div>
</x-layouts::app>
