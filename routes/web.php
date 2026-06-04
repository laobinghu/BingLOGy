<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

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

    Route::get('storage', \App\Livewire\Admin\Storage\Index::class)->name('storage.index');
    Route::get('storage/create', \App\Livewire\Admin\Storage\Form::class)->name('storage.create');
    Route::get('storage/{disk}/edit', \App\Livewire\Admin\Storage\Form::class)->name('storage.edit');
});

require __DIR__.'/settings.php';
