<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\TagAnalytics;
use App\Services\TagAnalyticsService;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tag::with('parent');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('slug', 'like', '%'.$search.'%')
                  ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $tags = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $parents = Tag::root()->ordered()->get();

        $totalTags = Tag::count();
        $activeTags = Tag::where('posts_count', '>', 0)->count();
        $trendingTags = app(TagAnalyticsService::class)->getTrending('weekly', 5);

        return view('admin.tags.index', compact('tags', 'parents', 'totalTags', 'activeTags', 'trendingTags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:tags,name|regex:/^[\p{L}\p{N}\x{4e00}-\x{9fff}_\-\s]+$/u',
            'color' => 'nullable|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:tags,id',
        ]);

        Tag::create($validated);

        return redirect()->route('admin.tags.index')->with('success', '标签已创建！');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:tags,name,'.$tag->id.'|regex:/^[\p{L}\p{N}\x{4e00}-\x{9fff}_\-\s]+$/u',
            'slug' => [
                'nullable',
                'max:255',
                'unique:tags,slug,'.$tag->id,
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'color' => 'nullable|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:tags,id|not_in:'.$tag->id,
        ]);

        $tag->update($validated);

        return redirect()->route('admin.tags.index')->with('success', '标签「'.$tag->name.'」已更新！');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $name = $tag->name;
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', '标签「'.$name.'」已删除！');
    }

    public function merge(Request $request, TagService $tagService): RedirectResponse
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:tags,id',
            'target_id' => 'required|exists:tags,id|different:source_id',
        ]);

        $target = $tagService->merge((int) $validated['source_id'], (int) $validated['target_id']);

        return redirect()->route('admin.tags.index')->with(
            'success',
            '标签已合并到「'.$target->name.'」！'
        );
    }

    public function reorder(Request $request): JsonResponse
    {
        $order = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:tags,id',
        ])['order'];

        foreach ($order as $index => $id) {
            Tag::where('id', $id)->updateQuietly(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
