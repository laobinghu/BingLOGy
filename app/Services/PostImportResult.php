<?php

namespace App\Services;

use Illuminate\Support\Str;

class PostImportResult
{
    /**
     * @param  array<string, mixed>  $meta
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public ?string $sourcePath = null,
        public array $meta = [],
        public string $body = '',
        public array $errors = [],
    ) {}

    public function title(): ?string
    {
        $title = $this->meta['title'] ?? null;

        return is_string($title) && $title !== '' ? $title : null;
    }

    public function slug(): ?string
    {
        $slug = $this->meta['slug'] ?? null;

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    public function excerpt(): ?string
    {
        $excerpt = $this->meta['excerpt'] ?? null;

        return is_string($excerpt) && $excerpt !== '' ? $excerpt : null;
    }

    public function coverImage(): ?string
    {
        $cover = $this->meta['cover_image'] ?? null;

        return is_string($cover) && $cover !== '' ? $cover : null;
    }

    public function published(): bool
    {
        return (bool) ($this->meta['published'] ?? false);
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        $tags = $this->meta['tags'] ?? [];

        return is_array($tags) ? array_values(array_filter(array_map('strval', $tags))) : [];
    }

    public function date(): ?\DateTimeInterface
    {
        $date = $this->meta['date'] ?? null;

        if ($date instanceof \DateTimeInterface) {
            return $date;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function extraMeta(): array
    {
        $reserved = array_flip([
            'title', 'slug', 'date', 'tags', 'excerpt', 'published', 'cover_image',
        ]);

        return array_diff_key($this->meta, $reserved);
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function shortBody(int $limit = 160): string
    {
        $plain = trim((string) preg_replace('/\s+/', ' ', strip_tags($this->body)));

        return Str::limit($plain, $limit);
    }

    public function displayTitle(): string
    {
        return $this->title() ?? '(无标题)';
    }

    public function displayDate(): ?string
    {
        $date = $this->date();

        return $date ? $date->format('Y-m-d H:i') : null;
    }
}
