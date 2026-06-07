@props([
    'name' => 'body',
    'value' => '',
    'id' => null,
    'height' => '420px',
])

<div
    x-data="milkdownEditor({ initial: @js((string) $value), name: @js($name) })"
    x-init="mount($refs.root)"
    class="markdown-editor rounded-lg border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-stone-900"
    data-markdown-editor
    data-name="{{ $name }}"
>
    <div
        x-ref="root"
        data-placeholder="开始写..."
        class="milkdown-host min-h-[420px] px-4 py-3"
        style="min-height: {{ $height }};"
    ></div>
    <input
        type="hidden"
        name="{{ $name }}"
        :value="markdown"
        {{ $attributes->except(['wire:model', 'name', 'value', 'id', 'class']) }}
    />
</div>

@once
    @vite(['resources/js/editor.js'])
@endonce
