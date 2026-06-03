<?php

use App\Http\Controllers\PostController;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $posts = Post::whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->orderBy('published_at', 'desc')
        ->limit(7)
        ->get();

    $featured = $posts->shift();

    return view('welcome', compact('posts', 'featured'));
})->name('home');

Route::get('/feed', function () {
    $posts = Post::whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->orderBy('published_at', 'desc')
        ->limit(20)
        ->get();

    return response()
        ->view('feed', ['posts' => $posts])
        ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
})->name('feed');

Route::get('/dashboard', function () {
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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('posts', PostController::class)->only(['index', 'show']);

Route::middleware('auth')->group(function () {
    Route::get('admin/posts', [PostController::class, 'adminIndex'])->name('admin.posts.index');

    Route::resource('admin/posts', PostController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->names([
            'create' => 'admin.posts.create',
            'store' => 'admin.posts.store',
            'edit' => 'admin.posts.edit',
            'update' => 'admin.posts.update',
            'destroy' => 'admin.posts.destroy',
        ]);
});

require __DIR__.'/settings.php';
