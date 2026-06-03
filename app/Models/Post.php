<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'slug', 'body', 'excerpt', 'published_at'];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (! $post->slug) {
                $slug = Str::slug($post->title);
                $post->slug = $slug ?: md5($post->title.microtime());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
