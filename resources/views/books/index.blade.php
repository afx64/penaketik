@extends('layouts.public')

@section('title', 'Bookstore · '.config('app.name'))
@section('meta_description', 'Browse every published book on PenakeTik. Read the first three chapters of any title for free.')

@section('content')
    <section class="shell section">
        <div class="section-head">
            <div>
                <span class="eyebrow">The bookstore</span>
                <h1 class="mb-0" style="font-size:clamp(2rem,4vw,2.8rem)">Every book on the shelf</h1>
                <p style="margin-top:.6rem">
                    {{ $books->total() }} {{ \Illuminate\Support\Str::plural('title', $books->total()) }} ·
                    first three chapters open to everyone
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('books.index') }}" class="flex wrap gap-1 items-center mb-2" style="margin-bottom:1.75rem">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title, synopsis or author…" style="max-width:340px">
            <select name="genre" style="max-width:200px">
                <option value="">All genres</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm" type="submit">Search</button>
            @if (request()->filled('q') || request()->filled('genre'))
                <a class="link-quiet" href="{{ route('books.index') }}">Clear</a>
            @endif
        </form>

        @if ($books->isEmpty())
            <div class="empty">
                <h3>No books matched</h3>
                <p>Try a different search term, or clear the filters to see everything on the shelf.</p>
                <a class="btn btn-sm mt-2" href="{{ route('books.index') }}">Show all books</a>
            </div>
        @else
            <div class="book-grid">
                @foreach ($books as $book)
                    @include('partials.book-card', ['book' => $book])
                @endforeach
            </div>

            {{ $books->links() }}
        @endif
    </section>
@endsection
