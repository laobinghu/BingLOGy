<?php

namespace App\Livewire\Admin\ImportExport;

use App\Models\Post;
use App\Services\PostImporter;
use App\Services\PostImportResult;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Symfony\Component\Yaml\Yaml;

#[Layout('livewire.admin.layout')]
class Index extends Component
{
    use WithFileUploads;

    public string $tab = 'paste';

    public string $rawPaste = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploadedFiles = [];

    /** @var array<int, array<string, mixed>> */
    public array $parsed = [];

    public array $selected = [];

    public bool $publishOnImport = false;

    public function mount(?string $tab = null, ?string $raw = null): void
    {
        if (in_array($tab, ['paste', 'upload', 'export'], true)) {
            $this->tab = $tab;
        }

        if ($raw !== null && $raw !== '') {
            $this->rawPaste = $raw;
            $this->parsePaste();
        }

        $this->resetSelection();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function updatedRawPaste(): void
    {
        $this->parsePaste();
    }

    public function parsePaste(): void
    {
        if (trim($this->rawPaste) === '') {
            $this->parsed = [];
            $this->selected = [];

            return;
        }

        $chunks = preg_split("/(?:^|\n)---[\s-]+(?:title|slug):/u", "\n".$this->rawPaste);
        $chunks = array_values(array_filter(array_map('trim', $chunks), fn ($c) => $c !== ''));

        $importer = app(PostImporter::class);
        $results = [];
        foreach ($chunks as $i => $chunk) {
            $result = $importer->fromString($chunk, 'pasted-'.($i + 1));
            $results[] = $this->serialize($result);
        }

        $this->parsed = $results;
        $this->resetSelection();
    }

    public function parseUpload(): void
    {
        $this->validate([
            'uploadedFiles.*' => 'file|mimes:md,markdown,zip',
        ]);

        $importer = app(PostImporter::class);
        $all = [];

        foreach ($this->uploadedFiles as $file) {
            if (str_ends_with(strtolower($file->getClientOriginalName()), '.zip')) {
                foreach ($importer->fromZip($file) as $r) {
                    $all[] = $this->serialize($r);
                }
            } else {
                $all[] = $this->serialize($importer->fromFile($file));
            }
        }

        $this->parsed = $all;
        $this->resetSelection();
        $this->uploadedFiles = [];
    }

    public function importSelected(): void
    {
        if (empty($this->selected)) {
            session()->flash('error', '请至少选择一条导入。');

            return;
        }

        $importer = app(PostImporter::class);
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($this->selected as $key) {
            if (! isset($this->parsed[$key])) {
                continue;
            }

            $payload = $this->parsed[$key];
            $result = $importer->fromString($payload['raw'], $payload['source']);

            if ($result->hasErrors()) {
                $skipped++;
                $errors[] = $payload['source'].'：'.implode('；', $result->errors);

                continue;
            }

            [$ok] = $importer->create($result, $this->publishOnImport);
            if ($ok) {
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->parsed = [];
        $this->selected = [];

        $message = "导入完成：成功 {$created} 篇，跳过 {$skipped} 篇。";
        if (! empty($errors)) {
            $message .= "\n".implode("\n", $errors);
        }

        session()->flash('success', $message);
        $this->dispatch('import-done');
    }

    public function toggleAll(bool $checked): void
    {
        if ($checked) {
            $this->selected = array_keys(array_filter($this->parsed, fn ($r) => empty($r['errors'])));
        } else {
            $this->selected = [];
        }
    }

    public function resetSelection(): void
    {
        $this->selected = array_values(array_filter(array_map(
            fn ($i, $r) => empty($r['errors']) ? $i : null,
            array_keys($this->parsed),
            $this->parsed,
        )));
    }

    public function render()
    {
        $exportable = Post::query()
            ->with('tags')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get();

        return view('livewire.admin.import-export.index', [
            'exportable' => $exportable,
        ]);
    }

    private function serialize(PostImportResult $result): array
    {
        return [
            'raw' => "---\n".Yaml::dump(array_intersect_key($result->meta, array_flip([
                'title', 'slug', 'date', 'tags', 'excerpt', 'published', 'cover_image',
            ])) + $result->extraMeta(), 6, 2)."\n---\n".$result->body,
            'source' => $result->sourcePath ?? '(pasted)',
            'title' => $result->title(),
            'date' => $result->displayDate(),
            'tags' => $result->tags(),
            'excerpt' => $result->excerpt(),
            'preview' => $result->shortBody(120),
            'errors' => $result->errors,
        ];
    }
}
