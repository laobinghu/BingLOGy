@php
    $siteName = \App\Services\SettingsManager::siteName();
@endphp

@isset($title)
    <title>{{ $title }} - {{ $siteName }}</title>
@else
    <title>{{ $siteName }}</title>
@endisset

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="alternate" type="application/atom+xml" title="{{ $siteName }}" href="{{ route('feed') }}">
@if (request()->routeIs('tags.show'))
    <link rel="alternate" type="application/atom+xml" title="{{ $tag->name }} - {{ $siteName }}" href="{{ route('tags.feed', $tag->slug) }}">
@endif

@fonts
@vite(['resources/css/app.css'])
@fluxAppearance

@php
    $customHead = \App\Services\SettingsManager::get('custom_head');
@endphp
@if (!empty($customHead))
    {!! $customHead !!}
@endif

@isset($post)
    @php
        \App\Services\HookManager::doAction('post.show.head', $post);
    @endphp
@endisset

@stack('meta')
