<?php

namespace App\Console\Commands;

use App\Models\Tag;
use App\Services\TagDeduplicator;
use App\Services\TagService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

#[Signature('tags:detect-duplicates {--auto-merge : Automatically merge duplicates when safe}')]
#[Description('Detect potentially duplicate tags based on name similarity')]
class DetectDuplicateTags extends Command
{
    public function handle(TagDeduplicator $deduplicator, TagService $tagService): int
    {
        $duplicates = $deduplicator->detect();

        if ($duplicates->isEmpty()) {
            $this->components->success('No duplicate tags found.');

            return self::SUCCESS;
        }

        $this->components->warn("Found {$duplicates->count()} potential duplicate pair(s):");
        $this->newLine();

        $merged = 0;

        foreach ($duplicates as $pair) {
            $source = $pair['source'];
            $target = $pair['target'];

            $this->line(sprintf(
                '  <fg=yellow>%s</> (posts: %d) → <fg=green>%s</> (posts: %d)  [score: %d]',
                $source->name,
                $source->posts_count,
                $target->name,
                $target->posts_count,
                $pair['score'],
            ));

            if ($this->option('auto-merge')) {
                if (Tag::whereIn('id', [$source->id, $target->id])->count() === 2) {
                    try {
                        $tagService->merge($source->id, $target->id);
                        $merged++;
                    } catch (ModelNotFoundException) {
                        $this->line('    <fg=red>skipped</> (tag no longer exists)');
                    }
                } else {
                    $this->line('    <fg=red>skipped</> (tag no longer exists)');
                }
            }
        }

        if ($this->option('auto-merge')) {
            $this->components->success("Merged {$merged} duplicate pair(s).");
        } else {
            $this->components->info('Use --auto-merge to automatically merge duplicates.');
        }

        return self::SUCCESS;
    }
}
