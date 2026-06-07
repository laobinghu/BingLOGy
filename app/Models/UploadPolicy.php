<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadPolicy extends Model
{
    protected $fillable = [
        'key',
        'label',
        'storage_strategy_id',
        'path_prefix',
        'allowed_mimes',
        'max_size_kb',
        'is_active',
    ];

    protected $casts = [
        'allowed_mimes' => 'array',
        'max_size_kb' => 'integer',
        'is_active' => 'boolean',
    ];

    public function storageStrategy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StorageStrategy::class);
    }
}
