@php
    $pageTitle = trim($__env->yieldContent('title'));
    $pageTitle = $pageTitle !== '' ? $pageTitle : (($site['seo_site_title'] ?? '') ?: config('app.name'));
    $pageDescription = trim($__env->yieldContent('meta_description'));
    $pageDescription = $pageDescription !== '' ? $pageDescription : ($site['seo_site_description'] ?? '');
    $pageKeywords = trim($__env->yieldContent('meta_keywords'));
    $pageKeywords = $pageKeywords !== '' ? $pageKeywords : ($site['seo_site_keywords'] ?? '');
    $ogImage = trim($__env->yieldContent('og_image'));
    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ \App\Support\Settings::themeMode() }}" {!! \App\Support\Settings::brandStyleAttribute() !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    @if ($pageDescription)
        <meta name="description" content="{{ $pageDescription }}">
    @endif
    @if ($pageKeywords)
        <meta name="keywords" content="{{ $pageKeywords }}">
    @endif
    @if (($site['seo_google_verification'] ?? null))
        <meta name="google-site-verification" content="{{ $site['seo_google_verification'] }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    @include('partials.theme')
    <link rel="stylesheet" href="{{ asset('css/penaketik.css') }}">
    @include('partials.favicon')
    @stack('head')
</head>
<body>
    <header class="site-header">
        <div class="shell bar">
            <a class="brand" href="{{ route('home') }}">
                @include('partials.brand', ['subtitle' => 'Pena ke Tik'])
            </a>

            <nav class="site-nav">
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('books.index') }}" @if (request()->routeIs('books.*')) aria-current="page" @endif>Bookstore</a>
                <a href="{{ route('authors.index') }}" @if (request()->routeIs('authors.*')) aria-current="page" @endif>Authors</a>

                @auth
                    <a href="{{ route('library.index') }}" @if (request()->routeIs('library.*')) aria-current="page" @endif>My library</a>
                    @if (auth()->user()->isAuthor())
                        <a href="{{ route('dashboard.index') }}" @if (request()->routeIs('dashboard.*')) aria-current="page" @endif>Studio</a>
                    @else
                        <a href="{{ route('reader.index') }}" @if (request()->routeIs('reader.*')) aria-current="page" @endif>My account</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" style="display:inline">
                        @csrf
                        <button type="submit" class="link-quiet" style="background:none;border:0;padding:0;cursor:pointer;font:inherit">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a class="btn btn-sm" href="{{ route('register') }}">Start reading</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <div class="shell">
            @include('partials.flash')
        </div>

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="shell inner">
            <div>
                <strong>PenakeTik</strong>
                <p class="mb-0 mt-1">{{ $site['site_tagline'] ?? 'Stories, serialised at your own pace.' }}</p>
            </div>
            <div>
                <p class="mb-0">PenakeTik is a product of <a href="https://warungserver.net" target="_blank" rel="noopener noreferrer">warungserver</a></p>
                <p class="mb-0 tiny mt-1">Every chapter is watermarked for the reader who opened it.</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/penaketik.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
