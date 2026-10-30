@extends('layouts.public')

@section('title', $chapter->title.' · '.$book->title)
@section('meta_description', 'This chapter of '.$book->title.' is restricted.')

@section('content')
    <section class="shell section">
        <div class="locked-panel">
            <div class="lock-badge" aria-hidden="true">🔒</div>

            @if ($reason === 'grant')
                <span class="eyebrow">Limited free access</span>
                <h1>Reserved for invited readers</h1>
                <p>
                    “{{ $chapter->title }}” is part of the limited-free edition of <strong>{{ $book->title }}</strong>.
                    {{ $book->user->displayName() }} grants access to individual readers by email.
                </p>
                @guest
                    <p class="small">Already invited? Log in with the email address the author added.</p>
                    <div class="locked-actions">
                        <a class="btn btn-accent" href="{{ route('login') }}">Log in</a>
                        <a class="btn btn-ghost" href="{{ route('register') }}">Create an account</a>
                    </div>
                @else
                    <p class="small">
                        Your account ({{ auth()->user()->email }}) does not have an invitation for this book yet.
                        If the author added a different address, ask them to grant it to this one.
                    </p>
                    <div class="locked-actions">
                        <a class="btn" href="{{ route('books.show', $book) }}">Back to the book</a>
                    </div>
                @endguest

            @elseif ($reason === 'purchase')
                <span class="eyebrow">Paid chapter</span>
                <h1>This chapter is part of the paid edition</h1>
                <p>
                    Buy <strong>{{ $book->title }}</strong> once and the whole book unlocks in your library,
                    including future chapters marked as paid.
                </p>
                <div class="price" style="margin:1rem 0">
                    <small>Full book</small>
                    {{ $book->priceLabel() }}
                </div>
                <div class="locked-actions">
                    @auth
                        <form method="POST" action="{{ route('orders.store', $book) }}">
                            @csrf
                            <button class="btn btn-accent" type="submit">Buy and unlock</button>
                        </form>
                    @else
                        <a class="btn btn-accent" href="{{ route('login') }}">Log in to buy</a>
                    @endauth
                    <a class="btn btn-ghost" href="{{ route('books.show', $book) }}">Back to the book</a>
                </div>

            @else
                <span class="eyebrow">Members only</span>
                <h1>Sign in to keep reading</h1>
                <p>
                    “{{ $chapter->title }}” is free for registered readers. The first
                    {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} chapters stay open to everyone; everything after that
                    needs a free account.
                </p>
                <div class="locked-actions">
                    <a class="btn btn-accent" href="{{ route('login') }}">Log in</a>
                    <a class="btn btn-ghost" href="{{ route('register') }}">Create a free account</a>
                </div>
            @endif
        </div>

        @if ($chapters->isNotEmpty())
            <div class="mt-4">
                <h2 style="font-size:1.3rem">In this book</h2>
                <ul class="chapter-list">
                    @foreach ($chapters as $item)
                        @php($readable = $item->readableWith($pass))
                        <li>
                            @if ($readable)
                                <a class="chapter-row" href="{{ route('books.read', [$book, $item]) }}">
                                    <span class="chapter-pos">{{ $item->position }}</span>
                                    <span class="chapter-name">{{ $item->title }}</span>
                                    <span class="badge badge-{{ $item->access }}">{{ $item->accessLabel() }}</span>
                                </a>
                            @else
                                <span class="chapter-row chapter-locked">
                                    <span class="chapter-pos">{{ $item->position }}</span>
                                    <span class="chapter-name">{{ $item->title }}</span>
                                    <span class="badge badge-{{ $item->access }}">{{ $item->accessLabel() }}</span>
                                    <span aria-hidden="true">🔒</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
@endsection
