<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    $totalPosts = \App\Models\Post::count();
    $publishedPosts = \App\Models\Post::whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->count();
    $draftPosts = \App\Models\Post::whereNull('published_at')
        ->orWhere('published_at', '>', now())
        ->count();
    $recentPosts = \App\Models\Post::orderBy('created_at', 'desc')
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
