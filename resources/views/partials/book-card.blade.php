@php
    $chapterCount = $book->chapters_count ?? $book->chapters->count();
@endphp
<a class="book-card" href="{{ route('books.show', $book) }}">
    <div class="book-cover">
        @if ($book->coverUrl())
            <img src="{{ $book->coverUrl() }}" alt="Cover of {{ $book->title }}" loading="lazy">
        @else
            <div class="cover-fallback">
                <span class="cover-kicker">{{ $book->genre ?: 'PenakeTik' }}</span>
                <span class="cover-title">{{ $book->title }}</span>
                <span class="tiny">{{ $book->user->displayName() }}</span>
            </div>
        @endif
    </div>
    <div class="book-meta">
        <p class="book-title">{{ $book->title }}</p>
        <p class="book-author">{{ $book->user->displayName() }} · {{ $chapterCount }} {{ Str::plural('chapter', $chapterCount) }}</p>
        <div class="book-tags">
            @if ((float) $book->price > 0)
                <span class="badge badge-paid">{{ $book->priceLabel() }}</span>
            @else
                <span class="badge badge-free">Free to read</span>
            @endif
            @if ($book->is_featured)
                <span class="badge">Featured</span>
            @endif
        </div>
    </div>
</a>
