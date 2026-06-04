@extends('layouts.public', ['title' => '页面未找到'])

@section('content')
    <div class="mx-auto max-w-5xl px-6 py-24 text-center">
        <p class="text-sm font-medium tracking-[0.22em] text-stone-500 uppercase dark:text-stone-400">
            404
        </p>
        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl dark:text-stone-50">
            页面未找到
        </h1>
        <p class="mt-4 text-base text-stone-600 dark:text-stone-400">
            你找的页面不存在，可能已经被移除了。
        </p>
        <a
            href="{{ route('home') }}"
            class="mt-8 inline-flex items-center rounded-full bg-stone-900 px-6 py-3 text-sm font-medium text-stone-50 transition hover:bg-stone-700 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-300"
        >
            回到首页
        </a>
    </div>
@endsection
