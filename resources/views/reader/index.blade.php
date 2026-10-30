@extends('layouts.public')

@section('title', 'My account · '.config('app.name'))
@section('meta_description', 'The books you own, the books you like and the comments you have written.')

@section('content')
    <section class="shell section">
        <div class="section-head">
            <div>
                <span class="eyebrow">Reader account</span>
                <h1 class="mb-0" style="font-size:clamp(2rem,4vw,2.6rem)">Hello, {{ auth()->user()->displayName() }}</h1>
                <p style="margin-top:.5rem">Your books, your likes and your comments. This is your whole account.</p>
            </div>
            <a class="link-quiet" href="{{ route('books.index') }}">Browse the bookstore →</a>
        </div>

        <div class="stat-grid">
            <div class="stat">
                <span>Books owned</span>
                <strong>{{ $purchased->count() }}</strong>
            </div>
            <div class="stat">
                <span>Invitations</span>
                <strong>{{ $invited->count() }}</strong>
            </div>
            <div class="stat">
                <span>Liked books</span>
                <strong>{{ $liked->count() }}</strong>
            </div>
            <div class="stat">
                <span>Comments</span>
                <strong>{{ $commentCount }}</strong>
            </div>
        </div>
    </section>

    <section class="shell section" style="padding-top:0">
        <div class="section-head">
            <div>
                <span class="eyebrow">Purchases</span>
                <h2>Books you bought</h2>
            </div>
            <a class="link-quiet" href="{{ route('library.index') }}">Open my library →</a>
        </div>

        @if ($purchased->isEmpty())
            <div class="empty">
                <h3>No purchases yet</h3>
                <p>Paid books appear here as soon as the author verifies your payment.</p>
                <a class="btn btn-sm mt-2" href="{{ route('books.index') }}">Find something to read</a>
            </div>
        @else
            <div class="book-grid">
                @foreach ($purchased as $book)
                    @include('partials.book-card', ['book' => $book])
                @endforeach
            </div>
        @endif
    </section>

    @if ($invited->isNotEmpty())
        <section class="shell section" style="padding-top:0">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Invitations</span>
                    <h2>Books the author opened for you</h2>
                </div>
            </div>

            <div class="book-grid">
                @foreach ($invited as $book)
                    @include('partials.book-card', ['book' => $book])
                @endforeach
            </div>
        </section>
    @endif

    <section class="shell section" style="padding-top:0">
        <div class="section-head">
            <div>
                <span class="eyebrow">Saved</span>
                <h2>Books you like</h2>
            </div>
        </div>

        @if ($liked->isEmpty())
            <div class="empty">
                <h3>Nothing liked yet</h3>
                <p>Tap the heart on any book page and it will be waiting here.</p>
            </div>
        @else
            <div class="book-grid">
                @foreach ($liked as $book)
                    @include('partials.book-card', ['book' => $book])
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell section" style="padding-top:0">
        <div class="section-head">
            <div>
                <span class="eyebrow">Payments</span>
                <h2>Recent orders</h2>
            </div>
        </div>

        @if ($orders->isEmpty())
            <p class="small muted">You have not ordered anything yet.</p>
        @else
            <div class="table-wrap panel" style="padding:.5rem">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Book</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="tiny">{{ $order->reference }}</td>
                                <td>{{ $order->book->title }}</td>
                                <td>{{ $order->amountLabel() }}</td>
                                <td>
                                    <span class="badge badge-{{ $order->status === 'paid' ? 'approved' : ($order->status === 'rejected' ? 'rejected' : 'pending') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td><a class="link-quiet" href="{{ route('orders.show', $order) }}">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="shell section" style="padding-top:0" id="comments">
        <div class="section-head">
            <div>
                <span class="eyebrow">Conversation</span>
                <h2>Your comments</h2>
            </div>
        </div>

        @if ($comments->isEmpty())
            <p class="small muted">You have not commented on a book yet.</p>
        @else
            <ul class="comment-list panel">
                @foreach ($comments as $comment)
                    <li class="comment">
                        <div class="comment-head">
                            <a href="{{ route('books.show', $comment->book) }}#comments">{{ $comment->book->title }}</a>
                            <span class="tiny muted">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="comment-body">{{ $comment->body }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
