@extends('layouts.public')

@section('title', 'My library · '.config('app.name'))
@section('meta_description', 'Books you can read on PenakeTik.')

@section('content')
    <section class="shell section">
        <div class="section-head">
            <div>
                <span class="eyebrow">Your shelf</span>
                <h1 class="mb-0" style="font-size:clamp(2rem,4vw,2.6rem)">My library</h1>
                <p style="margin-top:.5rem">Invited books and purchased books live here.</p>
            </div>
            <a class="link-quiet" href="{{ route('books.index') }}">Browse the bookstore →</a>
        </div>

        @if ($books->isEmpty())
            <div class="empty">
                <h3>Nothing unlocked yet</h3>
                <p>
                    You can read the first {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} chapters of any book in the
                    bookstore. Books appear here once an author invites you or a purchase is verified.
                </p>
                <a class="btn btn-sm mt-2" href="{{ route('books.index') }}">Find something to read</a>
            </div>
        @else
            <div class="book-grid">
                @foreach ($books as $book)
                    @include('partials.book-card', ['book' => $book])
                @endforeach
            </div>
        @endif
    </section>

    <section class="shell section" style="padding-top:0">
        <div class="section-head">
            <div>
                <span class="eyebrow">Payments</span>
                <h2>Your orders</h2>
            </div>
        </div>

        @if ($orders->isEmpty())
            <p class="small muted">No orders yet.</p>
        @else
            <div class="table-wrap panel" style="padding:.5rem">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Book</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
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
                                <td class="small muted">{{ $order->created_at->format('j M Y') }}</td>
                                <td><a class="link-quiet" href="{{ route('orders.show', $order) }}">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
