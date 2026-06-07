<flux:modal.trigger name="md-import">
    <flux:button variant="ghost" size="sm">
        从 Markdown 粘贴
    </flux:button>
</flux:modal.trigger>

<flux:modal name="md-import">
    <flux:heading size="lg">从 Markdown 粘贴</flux:heading>

    <form
        method="POST"
        action="{{ route('admin.import-export.preview') }}"
        class="mt-4 space-y-3"
    >
        @csrf

        <flux:textarea
            name="raw"
            label="粘贴 Markdown 内容（含 Front Matter）"
            rows="10"
            placeholder="---&#10;title: ...&#10;---&#10;正文..."
        />

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">取消</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">解析并填充</flux:button>
        </div>
    </form>
</flux:modal>
