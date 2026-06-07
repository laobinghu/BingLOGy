<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostImportExportController;
use App\Http\Controllers\TagController;
use App\Livewire\Admin\BlogSettings;
use App\Livewire\Admin\UploadPolicies\Form;
use App\Livewire\Admin\UploadPolicies\Index;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::middleware('check.maintenance')->group(function () {
    Route::get('/', function () {
        $posts = Post::with('tags')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(7)
            ->get();

        $featured = $posts->shift();

        return view('welcome', compact('posts', 'featured'));
    })->name('home');

    Route::get('/feed', function () {
        $posts = Post::with('tags')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        return response()
            ->view('feed', ['posts' => $posts])
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    })->name('feed');

    Route::get('/sitemap.xml', function () {
        $posts = Post::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()
            ->view('sitemap', ['posts' => $posts])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    })->name('sitemap');

    Route::resource('posts', PostController::class)->only(['index', 'show']);

    Route::post('posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        $totalPosts = Post::count();
        $publishedPosts = Post::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->count();
        $draftPosts = Post::whereNull('published_at')
            ->orWhere('published_at', '>', now())
            ->count();
        $recentPosts = Post::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('totalPosts', 'publishedPosts', 'draftPosts', 'recentPosts'));
    })->name('index');

    Route::get('posts', [PostController::class, 'adminIndex'])->name('posts.index');

    Route::resource('posts', PostController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->names([
            'create' => 'posts.create',
            'store' => 'posts.store',
            'edit' => 'posts.edit',
            'update' => 'posts.update',
            'destroy' => 'posts.destroy',
        ]);

    Route::get('tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('tags', [TagController::class, 'store'])->name('tags.store');
    Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

    Route::get('upload-policies', Index::class)->name('upload-policies.index');
    Route::get('upload-policies/create', Form::class)->name('upload-policies.create');
    Route::get('upload-policies/{policy}/edit', Form::class)->name('upload-policies.edit');

    Route::get('storage-strategies', App\Livewire\Admin\StorageStrategies\Index::class)->name('storage-strategies.index');
    Route::get('storage-strategies/create', App\Livewire\Admin\StorageStrategies\Form::class)->name('storage-strategies.create');
    Route::get('storage-strategies/{strategy}/edit', App\Livewire\Admin\StorageStrategies\Form::class)->name('storage-strategies.edit');

    Route::get('settings/blog', BlogSettings::class)->name('settings.blog');

    Route::get('comments', App\Livewire\Admin\Comments\Index::class)->name('comments.index');

    Route::get('plugins', App\Livewire\Admin\Plugins\Index::class)->name('plugins.index');

    Route::get('import-export', App\Livewire\Admin\ImportExport\Index::class)->name('import-export.index');
    Route::post('import-export/preview', [PostImportExportController::class, 'preview'])->name('import-export.preview');
    Route::get('import-export/export-all', [PostImportExportController::class, 'exportAll'])->name('import-export.export-all');
    Route::get('posts/{post}/export', [PostImportExportController::class, 'download'])->name('posts.export');
});

require __DIR__.'/settings.php';
