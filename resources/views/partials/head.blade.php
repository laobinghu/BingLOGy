@isset($title)
    <title>{{ $title }} - {{ config('app.name', 'BingLOGy') }}</title>
@else
    <title>{{ config('app.name', 'BingLOGy') }}</title>
@endisset

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="alternate" type="application/atom+xml" title="{{ config('app.name', 'BingLOGy') }}" href="{{ route('feed') }}">

@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
