@extends('layouts.reader')

@section('title', $chapter->title.' · '.$book->title)

@section('content')
    <div class="shell-narrow">
        <div class="reader-chapter-head">
            <span class="eyebrow">Chapter {{ $chapter->position }}</span>
            <h1>{{ $chapter->title }}</h1>
            <p class="small muted mb-0">
                {{ $book->title }} · {{ $book->user->displayName() }}
                @if ($chapter->access !== 'public')
                    · <span class="badge badge-{{ $chapter->access }}">{{ $chapter->accessLabel() }}</span>
                @endif
            </p>
        </div>

        <noscript>
            <p class="noscript-note">
                JavaScript is required to read on PenakeTik. Chapter protection and per-reader
                watermarking depend on it — please enable JavaScript and reload.
            </p>
        </noscript>

        <article
            class="reader-body"
            data-protected
            data-watermark="Licensed to {{ auth()->user()?->email ?? 'guest reader' }} · {{ request()->ip() }} · {{ now()->format('Y-m-d H:i') }}"
        >
            <div class="prose">
                {!! $chapter->content ?: '<p>This chapter is still empty.</p>' !!}
            </div>
        </article>

        <p class="tiny muted center mt-2">
            Licensed to {{ auth()->user()?->email ?? 'guest reader' }} on {{ now()->format('j M Y, H:i') }}.
            Copying, printing and screenshots are not permitted.
        </p>

        <nav class="reader-nav">
            @if ($previous)
                <a class="btn btn-ghost btn-sm" href="{{ route('books.read', [$book, $previous]) }}">← {{ $previous->title }}</a>
            @else
                <span></span>
            @endif

            <a class="link-quiet" href="{{ route('books.show', $book) }}">Table of contents</a>

            @if ($next)
                <a class="btn btn-sm" href="{{ route('books.read', [$book, $next]) }}">{{ $next->title }} →</a>
            @else
                <span class="small muted">You are at the latest chapter.</span>
            @endif
        </nav>
    </div>
@endsection
