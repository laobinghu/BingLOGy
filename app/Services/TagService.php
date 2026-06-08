<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use App\Models\TagAlias;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TagService
{
    public function __construct(
        private readonly TagAnalyticsService $analytics,
    ) {
    }
    /**
     * Parse a CSV string of tag names into a normalized array.
     *
     * Supports both English (,) and Chinese (，) commas.
     */
    public function parseTagNames(?string $csv): array
    {
        if (blank($csv)) {
            return [];
        }

        return array_values(
            array_filter(
                array_map('trim', preg_split('/[,，]/u', $csv) ?: [])
            )
        );
    }

    /**
     * Resolve tag names to tag IDs, creating new tags as needed.
     *
     * Supports alias matching: if a name matches an existing tag's alias,
     * the existing tag is used instead of creating a new one.
     *
     * @param  string[]  $names
     * @return int[]
     */
    public function resolveTagIds(array $names): array
    {
        $ids = [];

        foreach ($names as $name) {
            $name = trim($name);
            if (blank($name)) {
                continue;
            }

            $slug = Str::slug($name);
            $tag = null;

            // 1. Try exact slug match (for Latin-script tags)
            if ($slug) {
                $tag = Tag::where('slug', $slug)->first();
            }

            // 2. Try name match (for CJK or non-Latin tags)
            if (! $tag) {
                $tag = Tag::where('name', $name)->first();
            }

            // 3. Try alias match
            if (! $tag) {
                $tag = $this->findByAlias($name);
            }

            // 4. Create new tag if not found
            if (! $tag) {
                $tag = Tag::create([
                    'slug' => $slug ?: md5($name),
                    'name' => $name,
                ]);
            }

            $ids[] = $tag->id;
        }

        return $ids;
    }

    /**
     * Find a tag by its alias (case-insensitive).
     */
    private function findByAlias(string $name): ?Tag
    {
        $alias = TagAlias::where('alias', $name)->first();

        return $alias?->tag;
    }

    /**
     * Sync tags on a post from a CSV string of tag names.
     *
     * Parses names, resolves/firstOrCreate tags, syncs to pivot,
     * and refreshes cached posts_count on affected tags.
     */
    public function syncFromCsv(Post $post, ?string $csv): void
    {
        $names = $this->parseTagNames($csv);

        if (empty($names)) {
            $previousIds = $post->tags()->pluck('tags.id')->all();
            $post->tags()->sync([]);
            $this->syncCountsByIds($previousIds);

            return;
        }

        $tagIds = $this->resolveTagIds($names);
        $previousIds = $post->tags()->pluck('tags.id')->all();
        $post->tags()->sync($tagIds);

        // Refresh counts for both old and new tags
        $affectedIds = array_unique(array_merge($previousIds, $tagIds));
        $this->syncCountsByIds($affectedIds);
    }

    /**
     * Sync tags on a post from an array of tag IDs.
     *
     * Used when the form submits tag IDs directly (e.g. from a checkbox picker).
     */
    public function syncFromIds(Post $post, array $tagIds): void
    {
        $previousIds = $post->tags()->pluck('tags.id')->all();
        $post->tags()->sync($tagIds);

        $affectedIds = array_unique(array_merge($previousIds, $tagIds));
        $this->syncCountsByIds($affectedIds);
    }

    /**
     * Sync tags on a post from mixed input — either a CSV string or an array of IDs.
     */
    public function sync(Post $post, array|string|null $input): void
    {
        if (is_string($input)) {
            $this->syncFromCsv($post, $input);
        } elseif (is_array($input)) {
            // If array of strings (names), treat as CSV-like names
            $first = $input[0] ?? null;
            if ($first !== null && ! is_numeric($first)) {
                $this->syncFromCsv($post, implode(',', $input));
            } else {
                $this->syncFromIds($post, $input);
            }
        } else {
            $this->syncFromCsv($post, null);
        }
    }

    /**
     * Suggest tags matching the given query string.
     *
     * Searches name, slug, and aliases using efficient DB queries.
     *
     * @return Collection<int, Tag>
     */
    public function suggest(string $query): Collection
    {
        if (blank($query)) {
            return Tag::ordered()->limit(10)->get();
        }

        // Search name/slug with prefix match, plus alias match via subquery
        $tagIdsFromAlias = TagAlias::where('alias', 'like', $query.'%')
            ->pluck('tag_id')
            ->all();

        $tags = Tag::where(function ($q) use ($query) {
                $q->where('name', 'like', $query.'%')
                  ->orWhere('slug', 'like', $query.'%');
            })
            ->orWhereIn('id', $tagIdsFromAlias)
            ->ordered()
            ->limit(10)
            ->get();

        return $tags;
    }

    /**
     * Merge a source tag into a target tag.
     *
     * All posts from the source tag are moved to the target tag,
     * then the source tag is deleted. If the source tag has children,
     * they are reassigned to the target tag.
     */
    public function merge(int $sourceId, int $targetId): Tag
    {
        if ($sourceId === $targetId) {
            return Tag::findOrFail($targetId);
        }

        return DB::transaction(function () use ($sourceId, $targetId) {
            $source = Tag::findOrFail($sourceId);
            $target = Tag::findOrFail($targetId);

            // Reassign children of the source tag to the target tag
            if ($source->children()->exists()) {
                $source->children()->update(['parent_id' => $targetId]);
            }

            // Get all post IDs from the source tag
            $sourcePostIds = $source->posts()->pluck('posts.id')->all();

            // Attach source posts to target (ignore duplicates)
            $target->posts()->syncWithoutDetaching($sourcePostIds);

            // Detach source from all posts
            $source->posts()->detach();

            // Remember IDs before deleting source
            $affectedIds = [$sourceId, $targetId];

            // Delete the source tag
            $source->delete();

            // Refresh counts
            $this->syncCountsByIds($affectedIds);

            return $target->fresh(['parent', 'children']);
        });
    }

    /**
     * Refresh posts_count for a list of tag IDs.
     *
     * @param  int[]  $tagIds
     */
    public function syncCountsByIds(array $tagIds): void
    {
        $tagIds = array_unique(array_filter($tagIds));

        if (empty($tagIds)) {
            return;
        }

        Tag::whereIn('id', $tagIds)->get()->each(function (Tag $tag) {
            $count = $tag->posts()->count();
            $tag->updateQuietly([
                'posts_count' => $count,
            ]);
            if ($count > 0) {
                $this->analytics->recordPostCount($tag->id);
            }
        });
    }

    /**
     * Refresh posts_count for ALL tags.
     * Used by the sync-counts Artisan command.
     *
     * @param  string|null  $since  Only refresh tags updated after this date
     * @param  int  $chunk  Process tags in chunks to reduce memory usage
     */
    public function syncAllCounts(?string $since = null, int $chunk = 1000): int
    {
        $count = 0;

        $query = Tag::query();
        if ($since) {
            $query->where('updated_at', '>=', $since);
        }

        $query->chunkById($chunk, function ($tags) use (&$count) {
            foreach ($tags as $tag) {
                $tagCount = $tag->posts()->count();
                $tag->updateQuietly([
                    'posts_count' => $tagCount,
                ]);
                if ($tagCount > 0) {
                    $this->analytics->recordPostCount($tag->id);
                }
                $count++;
            }
        });

        return $count;
    }

}
