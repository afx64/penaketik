<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ \App\Support\Settings::themeMode() }}" {!! \App\Support\Settings::brandStyleAttribute() !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noarchive, nosnippet">
    <title>@yield('title', $book->title) · {{ config('app.name') }}</title>
    @include('partials.theme')
    <link rel="stylesheet" href="{{ asset('css/penaketik.css') }}">
    @include('partials.favicon')
    @stack('head')
</head>
<body>
    <header class="reader-header">
        <div class="shell bar">
            <a class="brand" href="{{ route('books.show', $book) }}">
                <span class="brand-mark">P</span>
                <span class="reader-title">
                    {{ $book->title }}
                    <small>{{ $book->user->displayName() }}</small>
                </span>
            </a>

            <nav class="site-nav">
                <a href="{{ route('books.show', $book) }}">Contents</a>
                @auth
                    <a href="{{ route('library.index') }}">My library</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="reader-wrap">
        @yield('content')
    </main>

    <div id="protection-veil" class="protection-veil print-blocked" hidden>
        <div>
            <div class="lock-icon" aria-hidden="true">🔒</div>
            <h2>Content protected</h2>
            <p data-veil-message>This chapter is licensed to you and cannot be copied or printed.</p>
            <p class="tiny">If this message stays up after closing developer tools, reload the page.</p>
        </div>
    </div>

    <div id="protection-toast" class="toast" role="status" aria-live="polite"></div>

    <script src="{{ asset('js/penaketik.js') }}" defer></script>
    <script src="{{ asset('js/reader-guard.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
