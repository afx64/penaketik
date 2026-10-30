@extends('layouts.dashboard')

@section('title', 'Books')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Library</span>
            <h1>Your books</h1>
            <p>Create a book, shape its chapters, then publish it to the bookstore.</p>
        </div>
        <a class="btn btn-accent" href="{{ route('dashboard.books.create') }}">+ New book</a>
    </div>

    @if ($books->isEmpty())
        <div class="empty">
            <h3>No books yet</h3>
            <p>A book holds your chapters, cover, pricing and access rules. Start with the title — you can change everything later.</p>
            <a class="btn mt-2" href="{{ route('dashboard.books.create') }}">Create your first book</a>
        </div>
    @else
        <div class="panel" style="padding:.5rem">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Chapters</th>
                            <th>Invites</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($books as $book)
                            <tr>
                                <td>
                                    <strong>{{ $book->title }}</strong>
                                    @if ($book->genre)
                                        <div class="tiny muted">{{ $book->genre }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $book->isPublished() ? 'badge-public' : 'badge-draft' }}">
                                        {{ ucfirst($book->status) }}
                                    </span>
                                </td>
                                <td class="small">{{ (float) $book->price > 0 ? $book->priceLabel() : 'Free' }}</td>
                                <td>{{ $book->chapters_count }}</td>
                                <td>{{ $book->grants_count }}</td>
                                <td class="small muted">{{ $book->updated_at->diffForHumans() }}</td>
                                <td class="flex gap-1">
                                    <a class="btn btn-ghost btn-sm" href="{{ route('dashboard.books.edit', $book) }}">Manage</a>
                                    <a class="icon-btn" href="{{ route('books.show', $book) }}" title="View public page">↗</a>
                                    <form method="POST" action="{{ route('dashboard.books.destroy', $book) }}" data-confirm="Delete “{{ $book->title }}” and all of its chapters? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn danger" type="submit" title="Delete book">✕</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
