<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use App\Support\FrontMatter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostImporter
{
    /**
     * Create a new PostImporter instance.
     */
    public function __construct(
        private readonly TagService $tagService,
    ) {}

    /**
     * Parse a single markdown document.
     */
    public function fromString(string $raw, ?string $sourcePath = null): PostImportResult
    {
        $raw = $this->normalize($raw);

        [$meta, $body] = FrontMatter::split($raw);

        $result = new PostImportResult(
            sourcePath: $sourcePath,
            meta: $meta,
            body: $body,
        );

        $this->validate($result);

        return $result;
    }

    public function fromFile(UploadedFile $file): PostImportResult
    {
        $contents = file_get_contents($file->getRealPath());

        return $this->fromString($contents === false ? '' : $contents, $file->getClientOriginalName());
    }

    /**
     * Parse a zip archive of .md files.
     *
     * @return array<int, PostImportResult>
     */
    public function fromZip(UploadedFile $file): array
    {
        $zip = new \ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            return [new PostImportResult(
                sourcePath: $file->getClientOriginalName(),
                meta: [],
                body: '',
                errors: ['无法打开 zip 文件。'],
            )];
        }

        $results = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'] ?? 'entry-'.$i;
            if (! str_ends_with(strtolower($name), '.md')) {
                continue;
            }
            $contents = $zip->getFromIndex($i);
            $results[] = $this->fromString($contents === false ? '' : $contents, $name);
        }

        $zip->close();

        return $results;
    }

    /**
     * Persist a parsed result as a Post (always as draft by default).
     *
     * @param  array<int, string>|null  $tags  override the tag list (e.g. user-toggled in preview);
     *                                         pass null to use whatever the front matter declared.
     * @return array{0: bool, 1: ?Post, 2: array<int, string>}
     */
    public function create(PostImportResult $result, bool $publish = false, ?array $tags = null): array
    {
        if ($result->hasErrors()) {
            return [false, null, $result->errors];
        }

        if (! $result->title()) {
            return [false, null, ['缺少 title 字段。']];
        }

        return DB::transaction(function () use ($result, $publish, $tags) {
            $slug = $this->resolveSlug($result);

            $post = Post::create([
                'user_id' => auth()->id() ?? 1,
                'title' => $result->title() ?? 'Untitled',
                'slug' => $slug,
                'body' => $result->body,
                'excerpt' => $result->excerpt(),
                'cover_image' => $result->coverImage(),
                'published_at' => $result->date()
                    ? ($publish || $result->published() ? $result->date() : null)
                    : ($publish ? now() : null),
                'meta' => $result->extraMeta(),
            ]);

            $this->syncTags($post, $tags ?? $result->tags());

            return [true, $post, []];
        });
    }

    public function syncTags(Post $post, array $tagNames): void
    {
        $tagIds = $this->tagService->resolveTagIds($tagNames);
        $post->tags()->sync($tagIds);
    }

    public function resolveSlug(PostImportResult $result): string
    {
        $base = $result->slug()
            ? Str::slug($result->slug())
            : Str::slug($result->title() ?? 'untitled');

        if (! $base) {
            $base = 'post-'.Str::random(6);
        }

        if (! Post::where('slug', $base)->exists()) {
            return $base;
        }

        return $base.'-'.Str::lower(Str::random(6));
    }

    private function validate(PostImportResult $result): void
    {
        if (empty($result->meta['title']) || ! is_string($result->meta['title'])) {
            $result->errors[] = '缺少 title 字段。';
        }

        if (trim($result->body) === '' && empty($result->meta['excerpt'])) {
            $result->errors[] = '正文为空，建议至少填写摘要。';
        }
    }

    private function normalize(string $raw): string
    {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        return str_replace(["\r\n", "\r"], "\n", $raw);
    }
}
