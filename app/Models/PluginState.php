<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PluginState extends Model
{
    protected $table = 'plugin_states';

    protected $fillable = [
        'plugin_name',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];
}
