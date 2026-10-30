@extends('layouts.public')

@section('title', $author->displayName().' · '.config('app.name'))
@section('meta_description', \Illuminate\Support\Str::limit($author->bio ?: 'Books by '.$author->displayName().' on PenakeTik.', 155))

@section('content')
    <section class="shell section" style="padding-bottom:1.5rem">
        <div class="flex gap-3 wrap items-center">
            @if ($author->avatarUrl())
                <img class="avatar avatar-lg" src="{{ $author->avatarUrl() }}" alt="{{ $author->displayName() }}">
            @else
                <span class="avatar avatar-lg">{{ $author->initials() }}</span>
            @endif

            <div class="grow" style="min-width:250px">
                <span class="eyebrow">Author profile</span>
                <h1 class="mb-0" style="font-size:clamp(1.9rem,4vw,2.7rem)">{{ $author->displayName() }}</h1>
                @if ($author->pen_name && $author->pen_name !== $author->name)
                    <p class="small muted mb-0">Writing as {{ $author->pen_name }}</p>
                @endif
                <p class="small muted">
                    {{ $books->count() }} published {{ \Illuminate\Support\Str::plural('book', $books->count()) }}
                    @if ($author->website)
                        · <a href="{{ $author->website }}" target="_blank" rel="noopener noreferrer">Website ↗</a>
                    @endif
                </p>
            </div>
        </div>

        @if ($author->bio)
            <p class="mt-3" style="max-width:46rem;color:var(--ink-soft)">{{ $author->bio }}</p>
        @endif

        @if ($storeLinks->isNotEmpty())
            <div class="store-links mt-2">
                @foreach ($storeLinks as $link)
                    <a class="store-link" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">
                        {{ $link->displayLabel() }} ↗
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell section" style="padding-top:1rem">
        <div class="section-head">
            <div>
                <span class="eyebrow">Works</span>
                <h2>Books by {{ $author->displayName() }}</h2>
            </div>
        </div>

        @if ($books->isEmpty())
            <div class="empty">
                <h3>No published books yet</h3>
                <p>This author has not published a book on PenakeTik yet.</p>
            </div>
        @else
            <div class="book-grid">
                @foreach ($books as $book)
                    @include('partials.book-card', ['book' => $book])
                @endforeach
            </div>
        @endif
    </section>
@endsection
