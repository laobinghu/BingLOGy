<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostExporter;
use App\Services\PostImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostImportExportController extends Controller
{
    public function download(Post $post, PostExporter $exporter)
    {
        $this->authorizePost($post);

        return $exporter->download($post);
    }

    public function exportAll(Request $request, PostExporter $exporter)
    {
        $this->authorizeAdmin();

        $ids = $request->query('ids');
        $query = Post::query()->with('tags')->orderByDesc('id');

        if (is_string($ids) && $ids !== '') {
            $idList = array_values(array_filter(array_map('intval', explode(',', $ids))));
            $query->whereIn('id', $idList);
        } elseif (auth()->user() && ! $request->boolean('all')) {
            $query->where('user_id', auth()->id());
        }

        $posts = $query->get();
        $zipName = 'posts-'.now()->format('Ymd-His').'.zip';

        return $exporter->exportManyZip($posts, $zipName);
    }

    public function preview(Request $request, PostImporter $importer): RedirectResponse
    {
        $this->authorizeAdmin();

        if (! $request->filled('raw')) {
            return back()->withErrors(['raw' => '请粘贴 Markdown 内容。']);
        }

        return redirect()->route('admin.import-export.index', ['tab' => 'paste', 'raw' => (string) $request->input('raw')]);
    }

    private function authorizePost(Post $post): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->id !== $post->user_id && ! $this->isAdmin($user)) {
            abort(403);
        }
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }
    }

    private function isAdmin($user): bool
    {
        return method_exists($user, 'hasRole')
            ? ($user->hasRole('admin') ?? false)
            : false;
    }
}
