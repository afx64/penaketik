<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ \App\Support\Settings::themeMode() }}" {!! \App\Support\Settings::brandStyleAttribute() !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <meta name="robots" content="noindex">
    @include('partials.theme')
    <link rel="stylesheet" href="{{ asset('css/penaketik.css') }}">
    @include('partials.favicon')
</head>
<body>
    <header class="site-header">
        <div class="shell bar">
            <a class="brand" href="{{ route('home') }}">
                @include('partials.brand', ['subtitle' => 'Pena ke Tik'])
            </a>
            <nav class="site-nav">
                <a href="{{ route('books.index') }}">Bookstore</a>
                @if (request()->routeIs('login'))
                    <a href="{{ route('register') }}">Create account</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                @endif
            </nav>
        </div>
    </header>

    <div class="auth-wrap">
        <div class="auth-card">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('js/penaketik.js') }}" defer></script>
</body>
</html>
