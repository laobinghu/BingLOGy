<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\TagAlias;

class Tag extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'color', 'icon',
        'sort_order', 'posts_count', 'meta', 'parent_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Tag $tag) {
            if (! $tag->slug) {
                $slug = Str::slug($tag->name);
                $tag->slug = $slug ?: md5($tag->name);
            }
        });
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(TagAlias::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sort_order' => 'integer',
            'posts_count' => 'integer',
        ];
    }

    /**
     * Scope a query to order tags by sort_order then name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope to only root (parent) tags.
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to only child tags of a given parent.
     */
    public function scopeChildrenOf(Builder $query, int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Check if this tag has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the aliases from the tag_aliases table.
     *
     * @return string[]
     */
    public function getAliasesAttribute(): array
    {
        if (! $this->relationLoaded('aliases')) {
            return $this->aliases()->pluck('alias')->all();
        }

        return $this->aliases->pluck('alias')->all();
    }

    /**
     * Set aliases from a string (comma-separated) or array.
     * Syncs the tag_aliases table.
     *
     * @param  string|string[]  $aliases
     */
    public function setAliases(array|string $aliases): void
    {
        if (is_string($aliases)) {
            $aliases = array_values(
                array_filter(
                    array_map('trim', preg_split('/[,，]/u', $aliases) ?: [])
                )
            );
        }

        $existingAliases = $this->aliases()->pluck('alias')->all();
        $toDelete = array_diff($existingAliases, $aliases);
        $toAdd = array_diff($aliases, $existingAliases);

        if (! empty($toDelete)) {
            $this->aliases()->whereIn('alias', $toDelete)->delete();
        }

        foreach ($toAdd as $alias) {
            $this->aliases()->create(['alias' => $alias]);
        }

        $this->load('aliases');
    }

    /**
     * Get the total posts count including all children tags.
     * Used for parent tag display on the public page.
     */
    public function totalPostsCount(): int
    {
        $tagIds = $this->children()->pluck('id')->push($this->id)->all();

        return \App\Models\Post::whereHas('tags', function ($q) use ($tagIds) {
            $q->whereIn('tags.id', $tagIds);
        })->count();
    }
}
