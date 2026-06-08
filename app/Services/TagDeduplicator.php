<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Collection;

class TagDeduplicator
{
    /**
     * Detect potential duplicate tags based on name similarity.
     *
     * @return Collection<int, array{source: Tag, target: Tag, score: float}>
     */
    public function detect(int $threshold = 3): Collection
    {
        $tags = Tag::all(['id', 'name', 'posts_count']);
        $duplicates = collect();

        foreach ($tags as $i => $source) {
            for ($j = $i + 1; $j < $tags->count(); $j++) {
                $target = $tags[$j];

                $score = $this->similarityScore($source->name, $target->name);

                if ($score >= $threshold) {
                    // Suggest merging the smaller tag into the larger one
                    [$src, $tgt] = $source->posts_count <= $target->posts_count
                        ? [$source, $target]
                        : [$target, $source];

                    $duplicates->push([
                        'source' => $src,
                        'target' => $tgt,
                        'score' => $score,
                    ]);
                }
            }
        }

        return $duplicates->sortByDesc('score')->values();
    }

    private function similarityScore(string $a, string $b): float
    {
        $a = mb_strtolower(trim($a));
        $b = mb_strtolower(trim($b));

        if ($a === $b) {
            return 100;
        }

        // Check if one contains the other
        if (str_contains($a, $b) || str_contains($b, $a)) {
            return 8;
        }

        // Check Levenshtein distance
        $len = max(mb_strlen($a), mb_strlen($b));
        $dist = levenshtein($a, $b);

        if ($len === 0) {
            return 0;
        }

        return $dist <= 3 ? (4 - $dist) : 0;
    }
}
