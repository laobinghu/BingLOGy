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

        return view('pages.posts.index', compact('posts', 'allTags'));
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

        return view('pages.posts.show', compact('post', 'comments'));
    }

    // 后台管理
    public function adminIndex(Request $request): View
    {
        $status = $request->query('status', 'all');

        $query = Post::query();

        if ($status === 'published') {
            $query->whereNotNull('published_at')->where('published_at', '<=', now());
        } elseif ($status === 'draft') {
            $query->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '>', now());
            });
        }

        $posts = $query->orderBy('created_at', 'desc')->get();
        $posts->load('tags');

        return view('admin.posts.index', compact('posts', 'status'));
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

        $validated['published_at'] = match ($request->input('publish_status')) {
            'published' => now(),
            'scheduled' => $request->date('published_at'),
            default => null,
        };

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

        $publishStatus = match (true) {
            $post->published_at && $post->published_at->isPast() => 'published',
            $post->published_at && $post->published_at->isFuture() => 'scheduled',
            default => 'draft',
        };

        return view('admin.posts.edit', compact('post', 'tags', 'publishStatus'));
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

        $post->published_at = match ($request->input('publish_status')) {
            'published' => $post->published_at ?? now(),
            'scheduled' => $request->date('published_at'),
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

    public function bulk(Request $request): RedirectResponse
    {
        $ids = array_values(array_filter(array_map('intval', (array) $request->input('ids', []))));

        if (empty($ids)) {
            return back()->with('error', '未选择任何文章。');
        }

        $posts = Post::whereIn('id', $ids)->get();

        if ($posts->isEmpty()) {
            return back()->with('error', '未找到可操作的文章。');
        }

        $action = $request->input('action', '');

        if ($action === 'publish') {
            $posts->each(fn ($p) => $p->update(['published_at' => $p->published_at ?? now()]));
            $label = '发布';
        } elseif ($action === 'unpublish') {
            $posts->each(fn ($p) => $p->update(['published_at' => null]));
            $label = '撤销发布';
        } elseif ($action === 'delete') {
            $posts->each(function ($p) {
                if ($p->cover_image) {
                    app(UploadPolicyService::class)->delete($p->cover_image, 'cover_image');
                }
                $p->delete();
            });
            $label = '删除';
        } elseif (in_array($action, ['tags_append', 'tags_replace', 'tags_remove'], true)) {
            $tagNames = array_values(array_filter(array_map('trim', preg_split('/[,，]/u', (string) $request->input('tags', '')) ?: [])));

            if (empty($tagNames)) {
                return back()->with('error', '标签操作需要填写标签名，逗号分隔。');
            }

            $tagIds = [];
            foreach ($tagNames as $name) {
                $slug = Str::slug($name);
                $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $name]);
                $tagIds[] = $tag->id;
            }

            $posts->load('tags');

            if ($action === 'tags_replace') {
                $posts->each(fn ($p) => $p->tags()->sync($tagIds));
                $label = '替换标签';
            } elseif ($action === 'tags_remove') {
                $posts->each(function ($p) use ($tagIds) {
                    $keep = $p->tags()->pluck('tags.id')->diff($tagIds)->values()->all();
                    $p->tags()->sync($keep);
                });
                $label = '移除标签';
            } else {
                $posts->each(fn ($p) => $p->tags()->attach(array_diff($tagIds, $p->tags()->pluck('tags.id')->all())));
                $label = '追加标签';
            }
        } else {
            return back()->with('error', '未知批量操作。');
        }

        return back()->with('success', '已'.$label.' '.$posts->count().' 篇文章。');
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
