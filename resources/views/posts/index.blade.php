<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章列表</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<div class="max-w-3xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold mb-8">BingLOGy</h1>
    @foreach ($posts as $post)
        <article class="mb-8">
            <h2 class="text-xl font-semibold">
                <a href="/posts/{{ $post->slug }}" class="text-blue-600 hover:underline">
                    {{ $post->title }}
                </a>
            </h2>
            <p class="text-gray-500 text-sm mt-1">
                {{ $post->published_at->format('Y-m-d') }}
            </p>
            @if ($post->excerpt)
                <p class="mt-2 text-gray-700">{{ $post->excerpt }}</p>
            @endif
        </article>
    @endforeach
</div>
</body>
</html>
