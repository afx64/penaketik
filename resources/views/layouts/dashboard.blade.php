<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ \App\Support\Settings::themeMode() }}" {!! \App\Support\Settings::brandStyleAttribute() !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <meta name="robots" content="noindex">
    @include('partials.theme')
    <link rel="stylesheet" href="{{ asset('css/penaketik.css') }}">
    @include('partials.favicon')
    @stack('head')
</head>
<body>
    @php($user = auth()->user())
    <header class="site-header">
        <div class="shell bar">
            <a class="brand" href="{{ route('dashboard.index') }}">
                @include('partials.brand', ['subtitle' => 'Author studio'])
            </a>
            <nav class="site-nav">
                <a href="{{ route('home') }}">View site</a>
                <a href="{{ route('library.index') }}">My library</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="link-quiet" style="background:none;border:0;padding:0;cursor:pointer;font:inherit">Log out</button>
                </form>
            </nav>
        </div>
    </header>

    <div class="shell dash">
        <aside class="dash-side">
            <div class="dash-user">
                @if ($user->avatarUrl())
                    <img class="avatar avatar-sm" src="{{ $user->avatarUrl() }}" alt="">
                @else
                    <span class="avatar avatar-sm">{{ $user->initials() }}</span>
                @endif
                <div class="who">
                    <strong>{{ $user->displayName() }}</strong>
                    <span>{{ $user->email }}</span>
                </div>
            </div>

            <nav class="dash-nav">
                <a href="{{ route('dashboard.index') }}" @if (request()->routeIs('dashboard.index')) class="active" @endif>Overview</a>
                <a href="{{ route('dashboard.books.index') }}" @if (request()->routeIs('dashboard.books.*') || request()->routeIs('dashboard.chapters.*') || request()->routeIs('dashboard.access.*')) class="active" @endif>Books &amp; chapters</a>
                <a href="{{ route('dashboard.orders.index') }}" @if (request()->routeIs('dashboard.orders.*')) class="active" @endif>Orders</a>

                <span class="section-label">Storefront</span>
                <a href="{{ route('dashboard.profile.edit') }}" @if (request()->routeIs('dashboard.profile.*')) class="active" @endif>Author profile</a>
                <a href="{{ route('dashboard.settings.branding') }}" @if (request()->routeIs('dashboard.settings.branding*')) class="active" @endif>Branding</a>
                <a href="{{ route('dashboard.settings.seo') }}" @if (request()->routeIs('dashboard.settings.seo*')) class="active" @endif>SEO</a>
                <a href="{{ route('dashboard.settings.mail') }}" @if (request()->routeIs('dashboard.settings.mail*')) class="active" @endif>Mail</a>
                <a href="{{ route('dashboard.settings.payment') }}" @if (request()->routeIs('dashboard.settings.payment*')) class="active" @endif>Payments</a>
            </nav>
        </aside>

        <section class="dash-main">
            @include('partials.flash')
            @yield('content')
        </section>
    </div>

    <div id="toast" class="toast" role="status" aria-live="polite"></div>
    <script src="{{ asset('js/penaketik.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
