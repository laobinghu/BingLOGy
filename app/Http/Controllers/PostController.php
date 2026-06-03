<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    // 前台
    public function index(): View
    {
        $posts = Post::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        abort_if(is_null($post->published_at) || $post->published_at->isFuture(), 404);

        return view('posts.show', compact('post'));
    }

    // 后台管理
    public function adminIndex(): View
    {
        $posts = Post::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.posts.index', compact('posts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'excerpt' => 'nullable',
            'published_at' => 'nullable|date',
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->boolean('publish') && ! $request->filled('published_at')) {
            $validated['published_at'] = now();
        }

        Post::create($validated);

        return redirect()->route('admin.posts.index')->with('success', '文章已创建！');
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'excerpt' => 'nullable',
            'published_at' => 'nullable|date',
        ]);

        unset($validated['published_at']);

        $post->fill($validated);

        $post->published_at = match (true) {
            $request->filled('published_at') => $request->date('published_at'),
            $request->boolean('publish') => $post->published_at ?? now(),
            default => null,
        };

        $post->save();

        return redirect()->route('admin.posts.index')->with('success', '文章已更新！');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', '文章已删除！');
    }
}
