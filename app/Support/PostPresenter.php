<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PostPresenter
{
    public static function wordCount(Post $post): int
    {
        return max(1, Str::wordCount(strip_tags($post->body)));
    }

    public static function readingTime(Post $post): int
    {
        return max(1, (int) ceil(self::wordCount($post) / 220));
    }

    public static function readingTimeLabel(Post $post): string
    {
        return self::readingTime($post).' 分钟阅读';
    }

    public static function excerpt(Post $post, int $limit = 200): string
    {
        if (! empty($post->excerpt)) {
            return $post->excerpt;
        }

        return Str::limit(preg_replace('/\s+/', ' ', strip_tags($post->body)), $limit);
    }

    /**
     * @param  Collection<int, Post>  $posts
     * @return Collection<int, array{year: int, posts: Collection<int, Post>}>
     */
    public static function groupByYear($posts)
    {
        return $posts
            ->groupBy(fn (Post $post) => (int) $post->published_at->format('Y'))
            ->sortKeysDesc()
            ->map(fn ($group, $year) => [
                'year' => (int) $year,
                'posts' => $group->values(),
            ])
            ->values();
    }
}
