<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageStrategy extends Model
{
    protected $fillable = [
        'key',
        'label',
        'driver',
        'config',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function uploadPolicies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UploadPolicy::class, 'storage_strategy_id');
    }
}
