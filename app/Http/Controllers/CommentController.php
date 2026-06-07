<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'body' => 'required|string|min:2|max:2000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $status = CommentModerationService::classify(
            $validated['body'],
            $request->ip()
        );

        $comment = Comment::create([
            'post_id' => $post->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'body' => $validated['body'],
            'status' => $status,
            'ip_address' => $request->ip(),
        ]);

        $message = $status === 'spam'
            ? '评论包含敏感内容，已被拦截。'
            : ($status === 'pending' ? '评论已提交，等待审核。' : '评论已发布。');

        return back()->with('success', $message);
    }

    public function approved(Post $post): View
    {
        $comments = Comment::approved()
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->approved()->orderBy('created_at', 'asc')])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('partials.comments-tree', ['comments' => $comments]);
    }
}
