@extends('layouts.public')

@section('title', ($site['seo_site_title'] ?? '') ?: config('app.name').' — an author-owned bookshop')

@section('content')
    <section class="shell hero">
        <div>
            <span class="eyebrow">Pena ke Tik · author-owned publishing</span>
            <h1>Write it. Publish it.<br>Keep it yours.</h1>
            <p class="hero-lede">
                PenakeTik is a shopfront and reading room for one writer — or a whole shelf of them.
                Publish free, limited-free and paid chapters, invite your readers, and get paid by bank transfer.
            </p>
            <div class="hero-actions">
                <a class="btn btn-accent" href="{{ route('books.index') }}">Browse the bookstore</a>
                @auth
                    <a class="btn btn-ghost" href="{{ route('dashboard.index') }}">Open your studio</a>
                @else
                    <a class="btn btn-ghost" href="{{ route('register') }}">Start publishing</a>
                @endauth
            </div>
        </div>

        @if ($author)
            <div class="author-card">
                @if ($author->avatarUrl())
                    <img class="avatar avatar-lg" src="{{ $author->avatarUrl() }}" alt="{{ $author->displayName() }}">
                @else
                    <span class="avatar avatar-lg">{{ $author->initials() }}</span>
                @endif
                <div class="name">{{ $author->displayName() }}</div>
                <p class="tiny muted mb-0">Author</p>
                <p class="bio">{{ \Illuminate\Support\Str::limit($author->bio ?: 'Writing one chapter at a time.', 160) }}</p>
                <a class="btn btn-ghost btn-sm" href="{{ route('authors.show', $author) }}">Visit profile</a>
                <div class="author-stats">
                    <div>
                        <strong>{{ $author->publishedBooks()->count() }}</strong>
                        books
                    </div>
                    <div>
                        <strong>{{ $books->sum('chapters_count') }}</strong>
                        chapters live
                    </div>
                </div>
            </div>
        @endif
    </section>

    <section class="shell section">
        <div class="section-head">
            <div>
                <span class="eyebrow">Recently shelved</span>
                <h2>New in the bookstore</h2>
                <p>Three chapters of every book are open to anyone. The rest opens with an account, an invitation or a purchase.</p>
            </div>
            <a class="link-quiet" href="{{ route('books.index') }}">See all books →</a>
        </div>

        @if ($books->isEmpty())
            <div class="empty">
                <h3>The shelves are waiting</h3>
                <p>No published books yet. The first author to publish appears right here.</p>
                @guest
                    <a class="btn mt-2" href="{{ route('register') }}">Create the first shelf</a>
                @endguest
            </div>
        @else
            <div class="book-grid">
                @foreach ($books as $book)
                    @include('partials.book-card', ['book' => $book])
                @endforeach
            </div>
        @endif
    </section>

    @if ($recentChapters->isNotEmpty())
        <section class="shell section">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Fresh pages</span>
                    <h2>Latest chapter updates</h2>
                </div>
            </div>

            <ul class="chapter-list">
                @foreach ($recentChapters as $chapter)
                    @php($readable = $chapter->readableWith($chapter->book->readerPass(auth()->user())))
                    <li>
                        <a class="chapter-row" href="{{ route('books.read', [$chapter->book, $chapter]) }}">
                            <span class="chapter-pos">{{ $chapter->position }}</span>
                            <span class="chapter-name">
                                {{ $chapter->title }}
                                <span class="chapter-excerpt">{{ $chapter->book->title }} · {{ $chapter->book->user->displayName() }}</span>
                            </span>
                            <span class="badge badge-{{ $chapter->access }}">{{ $chapter->accessLabel() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($storeLinks->isNotEmpty())
        <section class="shell section">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Elsewhere</span>
                    <h2>Also available in other stores</h2>
                </div>
            </div>
            <div class="store-links">
                @foreach ($storeLinks as $link)
                    <a class="store-link" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">
                        {{ $link->displayLabel() }} ↗
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
