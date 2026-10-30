@extends('layouts.public')

@section('title', 'Authors · '.config('app.name'))
@section('meta_description', 'Meet the writers publishing on PenakeTik.')

@section('content')
    <section class="shell section">
        <div class="section-head">
            <div>
                <span class="eyebrow">The people behind the books</span>
                <h1 class="mb-0" style="font-size:clamp(2rem,4vw,2.8rem)">Authors</h1>
            </div>
        </div>

        @if ($authors->isEmpty())
            <div class="empty">
                <h3>No authors yet</h3>
                <p>Once someone publishes a book, their profile appears here.</p>
            </div>
        @else
            <div class="book-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))">
                @foreach ($authors as $author)
                    <div class="panel">
                        @if ($author->avatarUrl())
                            <img class="avatar" src="{{ $author->avatarUrl() }}" alt="{{ $author->displayName() }}">
                        @else
                            <span class="avatar">{{ $author->initials() }}</span>
                        @endif
                        <h3 class="center" style="font-size:1.25rem">{{ $author->displayName() }}</h3>
                        <p class="center small muted">{{ $author->published_books_count }} {{ \Illuminate\Support\Str::plural('book', $author->published_books_count) }}</p>
                        @if ($author->bio)
                            <p class="small" style="color:var(--ink-soft)">{{ \Illuminate\Support\Str::limit($author->bio, 120) }}</p>
                        @endif
                        <a class="btn btn-ghost btn-sm btn-block" href="{{ route('authors.show', $author) }}">View profile</a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
