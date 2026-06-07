<?php

namespace App\Services;

use App\Models\Post;
use App\Support\FrontMatter;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExporter
{
    public function download(Post $post): StreamedResponse
    {
        $filename = $this->filename($post);
        $content = $this->render($post);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);
    }

    public function exportManyZip(Collection $posts, string $zipName = 'posts.zip'): StreamedResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'posts-').'.zip';

        $zip = new \ZipArchive;
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($posts as $post) {
            $zip->addFromString($this->filename($post), $this->render($post));
        }

        $zip->close();

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function render(Post $post): string
    {
        return FrontMatter::join($this->meta($post), $post->body ?? '');
    }

    public function filename(Post $post): string
    {
        $slug = $post->slug ?: 'post-'.$post->getKey();

        return $slug.'.md';
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(Post $post): array
    {
        $meta = [
            'title' => $post->title,
            'slug' => $post->slug,
            'date' => $post->published_at ?: now(),
            'tags' => $post->relationLoaded('tags')
                ? $post->tags->pluck('name')->all()
                : $post->tags()->pluck('name')->all(),
            'excerpt' => $post->excerpt,
            'published' => $post->published_at !== null && $post->published_at->isPast(),
            'cover_image' => $post->cover_image,
        ];

        if (is_array($post->meta)) {
            foreach ($post->meta as $key => $value) {
                if (! array_key_exists($key, $meta)) {
                    $meta[$key] = $value;
                }
            }
        }

        return $meta;
    }
}
