<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\Tag;

class TagObserver
{
    /**
     * Sync the posts_count for a tag.
     */
    public static function refreshCount(Tag $tag): void
    {
        $tag->updateQuietly([
            'posts_count' => $tag->posts()->count(),
        ]);
    }

    public function created(Tag $tag): void
    {
    }

    public function updated(Tag $tag): void
    {
    }

    public function deleted(Tag $tag): void
    {
    }

    /**
     * Sync counts for all tags affected by a post's tag changes.
     *
     * @param  int[]  $tagIds
     */
    public static function refreshPostTags(Post $post, array $tagIds = []): void
    {
        $ids = ! empty($tagIds) ? $tagIds : $post->tags()->pluck('tags.id')->all();

        if (! empty($ids)) {
            Tag::whereIn('id', $ids)->get()->each(function (Tag $tag) {
                static::refreshCount($tag);
            });
        }
    }
}
