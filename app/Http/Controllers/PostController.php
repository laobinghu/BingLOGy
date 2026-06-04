<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    // 前台
    public function index(Request $request): View
    {
        $query = Post::with('tags')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($tagSlug = $request->query('tag')) {
            $tag = Tag::where('slug', $tagSlug)->firstOrFail();
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id));
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate(20);
        $allTags = Tag::withCount('posts')->orderBy('name')->get();

        return view('posts.index', compact('posts', 'allTags'));
    }

    public function show(Post $post): View
    {
        abort_if(is_null($post->published_at) || $post->published_at->isFuture(), 404);

        $post->load('tags');
        $post->increment('views');

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
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->boolean('publish') && ! $request->filled('published_at')) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $post = Post::create($validated);
        $post->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.posts.index')->with('success', '文章已创建！');
    }

    public function create(): View
    {
        $tags = Tag::orderBy('name')->get();

        return view('admin.posts.create', compact('tags'));
    }

    public function edit(Post $post): View
    {
        $tags = Tag::orderBy('name')->get();

        return view('admin.posts.edit', compact('post', 'tags'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'excerpt' => 'nullable',
            'published_at' => 'nullable|date',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        unset($validated['published_at']);

        if ($request->boolean('remove_cover')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $validated['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        } else {
            unset($validated['cover_image']);
        }

        $post->fill($validated);

        $post->published_at = match (true) {
            $request->filled('published_at') => $request->date('published_at'),
            $request->boolean('publish') => $post->published_at ?? now(),
            default => null,
        };

        $post->save();
        $post->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.posts.index')->with('success', '文章已更新！');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', '文章已删除！');
    }
}
