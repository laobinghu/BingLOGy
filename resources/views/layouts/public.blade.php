@props(['title' => null])

<!DOCTYPE html>
<html lang="zh-CN" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        @include('partials.head')
        @vite(['resources/js/app.js'])
    </head>
    <body class="flex min-h-full flex-col bg-paper font-sans text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
        @include('partials.site-header')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('partials.site-footer')
        @include('partials.theme-toggle-script')
    </body>
</html>
