<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<div class="max-w-3xl mx-auto py-12 px-4">
    <a href="/posts" class="text-blue-600 hover:underline">&larr; 返回列表</a>
    <article class="mt-8">
        <h1 class="text-3xl font-bold mb-4">{{ $post->title }}</h1>
        <p class="text-gray-500 text-sm mb-8">
            {{ $post->published_at->format('Y-m-d') }}
        </p>
        <div class="prose max-w-none">
            {!! nl2br(e($post->body)) !!}
        </div>
    </article>
</div>
</body>
</html>
