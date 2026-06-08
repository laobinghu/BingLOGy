<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagAnalytics extends Model
{
    protected $fillable = [
        'tag_id', 'period', 'period_start', 'post_count', 'searches_count',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'post_count' => 'integer',
            'searches_count' => 'integer',
        ];
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
