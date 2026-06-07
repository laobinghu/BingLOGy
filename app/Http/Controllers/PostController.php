<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Services\SettingsManager;
use App\Services\UploadPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $perPage = SettingsManager::get('posts_per_page', 20);
        $posts = $query->orderBy('published_at', 'desc')->paginate((int) $perPage);
        $allTags = Tag::withCount('posts')->orderBy('name')->get();

        return view('posts.index', compact('posts', 'allTags'));
    }

    public function show(Post $post): View
    {
        abort_if(is_null($post->published_at) || $post->published_at->isFuture(), 404);

        $post->load('tags');
        $post->increment('views');

        $comments = Comment::approved()
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->approved()->orderBy('created_at', 'asc')])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('posts.show', compact('post', 'comments'));
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
            'slug' => 'nullable|max:255|unique:posts,slug',
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
            $validated['cover_image'] = app(UploadPolicyService::class)
                ->store($request->file('cover_image'), 'cover_image');
        }

        $post = Post::create($validated);
        $this->syncTags($post, $request);

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
            'slug' => 'nullable|max:255|unique:posts,slug,'.$post->id,
            'body' => 'required',
            'excerpt' => 'nullable',
            'published_at' => 'nullable|date',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        unset($validated['published_at']);

        if ($request->boolean('remove_cover')) {
            if ($post->cover_image) {
                app(UploadPolicyService::class)->delete($post->cover_image, 'cover_image');
            }
            $validated['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                app(UploadPolicyService::class)->delete($post->cover_image, 'cover_image');
            }
            $validated['cover_image'] = app(UploadPolicyService::class)
                ->store($request->file('cover_image'), 'cover_image');
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
        $this->syncTags($post, $request);

        return redirect()->route('admin.posts.index')->with('success', '文章已更新！');
    }

    private function syncTags(Post $post, Request $request): void
    {
        if ($request->filled('tags_csv')) {
            $names = array_values(array_filter(array_map('trim', preg_split('/[,，]/u', (string) $request->input('tags_csv')) ?: [])));
            $tagIds = [];
            foreach ($names as $name) {
                $slug = Str::slug($name);
                $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $name]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);

            return;
        }

        $post->tags()->sync($request->input('tags', []));
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->cover_image) {
            app(UploadPolicyService::class)->delete($post->cover_image, 'cover_image');
        }

        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', '文章已删除！');
    }
}
