<?php

use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Tag::whereNotNull('meta->aliases')->each(function (Tag $tag) {
            $aliases = $tag->meta['aliases'] ?? [];
            if (is_array($aliases) && ! empty($aliases)) {
                foreach ($aliases as $alias) {
                    if (is_string($alias) && trim($alias) !== '') {
                        $tag->aliases()->firstOrCreate(['alias' => trim($alias)]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('tag_aliases')->truncate();
    }
};
