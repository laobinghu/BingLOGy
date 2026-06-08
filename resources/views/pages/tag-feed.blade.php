<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{{ $tag->name }} - {{ \App\Services\SettingsManager::siteName() }}</title>
    <link href="{{ route('tags.feed', $tag->slug) }}" rel="self" />
    <link href="{{ route('tags.show', $tag->slug) }}" />
    <id>{{ route('tags.show', $tag->slug) }}</id>
    <updated>{{ $posts->first()?->published_at?->toAtomString() ?? now()->toAtomString() }}</updated>
    <generator>BingLOGy</generator>
    <language>zh-CN</language>

    @foreach ($posts as $post)
        <entry>
            <title>{{ $post->title }}</title>
            <link href="{{ route('posts.show', $post) }}" />
            <id>{{ route('posts.show', $post) }}</id>
            <updated>{{ $post->updated_at->toAtomString() }}</updated>
            <published>{{ $post->published_at->toAtomString() }}</published>
            <summary>{{ \App\Support\PostPresenter::excerpt($post, 280) }}</summary>
            <content type="html"><![CDATA[
                <h2>{{ $post->title }}</h2>
                {{ \App\Support\PostPresenter::bodyHtml($post) }}
            ]]></content>
        </entry>
    @endforeach
</feed>


