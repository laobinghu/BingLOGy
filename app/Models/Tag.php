<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::saving(function (Tag $tag) {
            if (! $tag->slug) {
                $slug = Str::slug($tag->name);
                $tag->slug = $slug ?: md5($tag->name.microtime());
            }
        });
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
