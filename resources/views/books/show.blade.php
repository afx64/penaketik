@extends('layouts.public')

@section('title', $book->metaTitle().' · '.config('app.name'))
@section('meta_description', $book->metaDescription())
@section('meta_keywords', $book->meta_keywords ?: '')
@section('og_type', 'book')
@if ($book->coverUrl())
    @section('og_image', $book->coverUrl())
@endif

@section('content')
    <div class="shell" style="padding-top:1.25rem">
        <p class="small muted">
            <a href="{{ route('books.index') }}">Bookstore</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('authors.show', $book->user) }}">{{ $book->user->displayName() }}</a>
            <span aria-hidden="true">/</span>
            {{ $book->title }}
        </p>
    </div>

    <section class="shell book-hero">
        <div class="book-cover">
            @if ($book->coverUrl())
                <img src="{{ $book->coverUrl() }}" alt="Cover of {{ $book->title }}">
            @else
                <div class="cover-fallback">
                    <span class="cover-kicker">{{ $book->genre ?: 'PenakeTik' }}</span>
                    <span class="cover-title">{{ $book->title }}</span>
                    <span class="tiny">{{ $book->user->displayName() }}</span>
                </div>
            @endif
        </div>

        <div>
            <span class="eyebrow">{{ $book->genre ?: 'Fiction' }}</span>
            <h1>{{ $book->title }}</h1>
            @if ($book->subtitle)
                <p class="book-subtitle">{{ $book->subtitle }}</p>
            @endif

            <div class="book-facts">
                <span>By <a href="{{ route('authors.show', $book->user) }}">{{ $book->user->displayName() }}</a></span>
                <span aria-hidden="true">·</span>
                <span>{{ $chapters->count() }} {{ \Illuminate\Support\Str::plural('chapter', $chapters->count()) }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ strtoupper($book->language) }}</span>
                @if (! $book->isPublished())
                    <span class="badge badge-draft">Draft — only you can see this</span>
                @endif
            </div>

            <p class="synopsis">{{ $book->synopsis }}</p>

            <div class="buy-box">
                @if ((float) $book->price > 0)
                    <div class="price">
                        <small>Full book</small>
                        {{ $book->priceLabel() }}
                    </div>
                @else
                    <div class="price">
                        <small>Access</small>
                        Free to read
                    </div>
                @endif

                @if ($isOwner)
                    <a class="btn" href="{{ route('dashboard.books.edit', $book) }}">Edit book</a>
                    <a class="btn btn-ghost" href="{{ route('dashboard.access.index', $book) }}">Manage access</a>
                @elseif ($pass['purchased'])
                    <a class="btn btn-accent" href="{{ route('books.read', [$book, $chapters->first()]) }}">Read the full book</a>
                    <span class="badge badge-approved">Purchased</span>
                @elseif ($pass['granted'])
                    <span class="badge badge-limited">Invited reader</span>
                    @if ($chapters->isNotEmpty())
                        <a class="btn btn-ghost" href="{{ route('books.read', [$book, $chapters->first()]) }}">Start reading</a>
                    @endif
                @elseif ((float) $book->price > 0)
                    @auth
                        @if ($order && $order->status === 'pending')
                            <a class="btn" href="{{ route('orders.show', $order) }}">Continue payment</a>
                        @else
                            <form method="POST" action="{{ route('orders.store', $book) }}">
                                @csrf
                                <button class="btn btn-accent" type="submit">Buy for {{ $book->priceLabel() }}</button>
                            </form>
                        @endif
                    @else
                        <a class="btn btn-accent" href="{{ route('login') }}">Log in to buy</a>
                    @endauth
                @endif
            </div>

            <div class="like-row">
                @auth
                    <form method="POST" action="{{ route('books.like', $book) }}">
                        @csrf
                        <button class="like-btn {{ $liked ? 'is-on' : '' }}" type="submit" aria-pressed="{{ $liked ? 'true' : 'false' }}">
                            <span aria-hidden="true">{{ $liked ? '♥' : '♡' }}</span>
                            {{ $liked ? 'Liked' : 'Like this book' }}
                            <span class="like-count">{{ $likeCount }}</span>
                        </button>
                    </form>
                @else
                    <a class="like-btn" href="{{ route('login') }}">
                        <span aria-hidden="true">♡</span> Log in to like it
                        <span class="like-count">{{ $likeCount }}</span>
                    </a>
                @endauth
            </div>

            @if (! $isOwner && $chapters->isNotEmpty() && ! $pass['purchased'])
                <p class="tiny muted mt-2 mb-0">
                    First {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} chapters are public previews.
                    Signing in unlocks free chapters; invited and paid chapters stay locked until granted.
                </p>
            @endif
        </div>
    </section>

    <section class="shell section" style="padding-top:1rem">
        <div class="section-head">
            <div>
                <span class="eyebrow">Contents</span>
                <h2>Chapters</h2>
            </div>
        </div>

        @if ($chapters->isEmpty())
            <div class="empty">
                <h3>No chapters yet</h3>
                <p>The author has not published any chapters for this book.</p>
            </div>
        @else
            <ul class="chapter-list">
                @foreach ($chapters as $chapter)
                    @php($readable = $chapter->readableWith($pass))
                    @php($reason = $chapter->lockReason($pass))
                    <li>
                        @if ($readable)
                            <a class="chapter-row" href="{{ route('books.read', [$book, $chapter]) }}">
                                <span class="chapter-pos">{{ $chapter->position }}</span>
                                <span class="chapter-name">
                                    {{ $chapter->title }}
                                    @if ($chapter->excerpt())
                                        <span class="chapter-excerpt">{{ $chapter->excerpt(110) }}</span>
                                    @endif
                                </span>
                                <span class="badge badge-{{ $chapter->access }}">{{ $chapter->accessLabel() }}</span>
                            </a>
                        @else
                            <span class="chapter-row chapter-locked">
                                <span class="chapter-pos">{{ $chapter->position }}</span>
                                <span class="chapter-name">
                                    {{ $chapter->title }}
                                    <span class="chapter-excerpt">
                                        @if ($reason === 'purchase')
                                            Unlocks after purchase
                                        @elseif ($reason === 'grant')
                                            Invited readers only
                                        @else
                                            Sign in to read
                                        @endif
                                    </span>
                                </span>
                                <span class="badge badge-{{ $chapter->access }}">{{ $chapter->accessLabel() }}</span>
                                <span aria-hidden="true" class="muted">🔒</span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($storeLinks->isNotEmpty())
            <div class="panel mt-4">
                <div class="panel-head">
                    <h3>Buy from other bookstores</h3>
                </div>
                <div class="store-links">
                    @foreach ($storeLinks as $link)
                        <a class="store-link" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">
                            {{ $link->displayLabel() }} ↗
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="shell section" style="padding-top:0" id="comments">
        <div class="section-head">
            <div>
                <span class="eyebrow">Conversation</span>
                <h2>Reader comments</h2>
            </div>
            <span class="small muted">{{ $commentCount }} {{ \Illuminate\Support\Str::plural('comment', $commentCount) }}</span>
        </div>

        @auth
            <form class="panel comment-form" method="POST" action="{{ route('comments.store', $book) }}">
                @csrf

                <label class="field">
                    <span>Add a comment <span class="hint">public, and attached to your account</span></span>
                    <textarea name="body" rows="3" maxlength="2000" required placeholder="What did you think of it?">{{ old('body') }}</textarea>
                    @error('body') <span class="error-text">{{ $message }}</span> @enderror
                </label>

                <div class="form-actions" style="margin-top:1rem;padding-top:0;border-top:0">
                    <button class="btn btn-accent btn-sm" type="submit">Post comment</button>
                </div>
            </form>
        @else
            <p class="small muted"><a href="{{ route('login') }}">Log in</a> to join the conversation.</p>
        @endauth

        @if ($comments->isEmpty())
            <p class="small muted mt-3">No comments yet. Be the first to say something.</p>
        @else
            <ul class="comment-list panel mt-2">
                @foreach ($comments as $comment)
                    <li class="comment">
                        <div class="comment-head">
                            <strong>{{ $comment->user->displayName() }}</strong>
                            @if ($comment->user->isAuthor())
                                <span class="badge badge-limited">Author</span>
                            @endif
                            <span class="tiny muted">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="comment-body">{{ $comment->body }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
