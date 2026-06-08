<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\TagAnalytics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TagAnalyticsService
{
    public function recordSearch(int $tagId): void
    {
        $this->record($tagId, 'searches_count');
    }

    public function recordPostCount(int $tagId): void
    {
        $this->record($tagId, 'post_count');
    }

    private function record(int $tagId, string $column): void
    {
        $periods = [
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
        ];

        foreach ($periods as $period => $start) {
            TagAnalytics::withoutTimestamps(function () use ($tagId, $period, $start, $column) {
                TagAnalytics::upsert(
                    [
                        'tag_id' => $tagId,
                        'period' => $period,
                        'period_start' => $start,
                        $column => 1,
                    ],
                    ['tag_id', 'period', 'period_start'],
                    [$column => DB::raw("$column + 1")]
                );
            });
        }
    }

    public function getTrending(string $period = 'weekly', int $limit = 10): Collection
    {
        $start = match ($period) {
            'daily' => now()->startOfDay(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };

        $analytics = TagAnalytics::with('tag')
            ->where('period', $period)
            ->where('period_start', $start)
            ->orderByDesc('post_count')
            ->orderByDesc('searches_count')
            ->limit($limit)
            ->get();

        return $analytics->map(fn ($a) => $a->tag);
    }

    public function getRelatedTags(int $tagId, int $limit = 10): Collection
    {
        $postIds = DB::table('post_tag')
            ->where('tag_id', $tagId)
            ->pluck('post_id');

        if ($postIds->isEmpty()) {
            return collect();
        }

        $relatedTagIds = DB::table('post_tag')
            ->whereIn('post_id', $postIds)
            ->where('tag_id', '!=', $tagId)
            ->groupBy('tag_id')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($limit)
            ->pluck('tag_id');

        if ($relatedTagIds->isEmpty()) {
            return collect();
        }

        return Tag::whereIn('id', $relatedTagIds)
            ->orderByRaw('FIELD(id,'.$relatedTagIds->implode(',').')')
            ->get();
    }
}
