<?php

namespace App\Console\Commands;

use App\Services\TagService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tags:sync-counts {--since= : Only refresh tags updated after this date (Y-m-d)}
                                 {--chunk=1000 : Process tags in chunks to reduce memory usage}')]
#[Description('Recalculate and backfill posts_count for all tags')]
class SyncTagCounts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TagService $tagService): int
    {
        $this->components->info('Syncing tag counts...');

        $since = $this->option('since');
        $chunk = (int) $this->option('chunk');

        $count = $tagService->syncAllCounts($since, $chunk);

        $this->components->success("Synced posts_count for {$count} tag(s).");

        return self::SUCCESS;
    }
}
