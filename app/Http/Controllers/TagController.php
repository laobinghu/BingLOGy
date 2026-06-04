<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('posts')->orderBy('name')->get();

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:tags,name',
        ]);

        Tag::create($validated);

        return redirect()->route('admin.tags.index')->with('success', '标签已创建！');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:tags,name,'.$tag->id,
        ]);

        $tag->update($validated);

        return redirect()->route('admin.tags.index')->with('success', '标签已更新！');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', '标签已删除！');
    }
}
