<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{{ config('app.name', 'BingLOGy') }}</title>
    <link href="{{ route('feed') }}" rel="self" />
    <link href="{{ route('home') }}" />
    <id>{{ route('home') }}</id>
    <updated>{{ $posts->first()?->published_at?->toAtomString() ?? now()->toAtomString() }}</updated>
    <generator>Laravel</generator>
    <language>zh-CN</language>

    @foreach ($posts as $post)
        <entry>
            <title>{{ $post->title }}</title>
            <link href="{{ route('posts.show', $post) }}" />
            <id>{{ route('posts.show', $post) }}</id>
            <updated>{{ $post->updated_at->toAtomString() }}</updated>
            <published>{{ $post->published_at->toAtomString() }}</published>
            <summary>{{ \App\Support\PostPresenter::excerpt($post, 280) }}</summary>
        </entry>
    @endforeach
</feed>
