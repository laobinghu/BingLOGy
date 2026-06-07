<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'post_id',
        'parent_id',
        'name',
        'email',
        'body',
        'status',
        'ip_address',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSpam($query)
    {
        return $query->where('status', 'spam');
    }
}
