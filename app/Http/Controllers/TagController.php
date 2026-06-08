<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Services\TagAnalyticsService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Resolve tag IDs including children for hierarchical tags.
     *
     * @return int[]
     */
    private function resolveTagIds(Tag $tag): array
    {
        if ($tag->hasChildren()) {
            return $tag->children()->pluck('id')->push($tag->id)->all();
        }

        return [$tag->id];
    }

    public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $tagIds = $this->resolveTagIds($tag);

        $posts = Post::with('tags')
            ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        $childrenTags = $tag->hasChildren() ? $tag->children()->ordered()->get() : collect();

        $relatedTags = ! $tag->hasChildren()
            ? app(TagAnalyticsService::class)->getRelatedTags($tag->id, 8)
            : collect();

        return view('pages.tags.show', compact('tag', 'posts', 'childrenTags', 'relatedTags'));
    }

    public function feed(string $slug): Response
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $tagIds = $this->resolveTagIds($tag);

        $posts = Post::with('tags')
            ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        return response()
            ->view('pages.tag-feed', compact('tag', 'posts'))
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }
}
