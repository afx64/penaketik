@extends('layouts.dashboard')

@section('title', 'Access · '.$book->title)

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Limited free access</span>
            <h1>{{ $book->title }}</h1>
            <p>Invite registered readers by email. They can open every chapter marked “Limited free”.</p>
        </div>
        <div class="flex gap-1">
            <a class="btn btn-ghost btn-sm" href="{{ route('dashboard.books.edit', $book) }}">Back to book</a>
            <a class="btn btn-ghost btn-sm" href="{{ route('books.show', $book) }}">View page ↗</a>
        </div>
    </div>

    <div class="editor-grid">
        <div class="panel">
            <div class="panel-head">
                <h2>Invited readers</h2>
                <span class="badge">{{ $grants->count() }}</span>
            </div>

            @if ($grants->isEmpty())
                <p class="small muted">Nobody invited yet. Add a reader's email on the right.</p>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reader</th>
                                <th>Note</th>
                                <th>Granted</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grants as $grant)
                                <tr>
                                    <td>
                                        <strong>{{ $grant->user->displayName() }}</strong>
                                        <div class="tiny muted">{{ $grant->user->email }}</div>
                                    </td>
                                    <td class="small muted">{{ $grant->note ?: '—' }}</td>
                                    <td class="small muted">{{ $grant->granted_at?->format('j M Y') ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('dashboard.access.destroy', [$book, $grant->user]) }}" data-confirm="Revoke access for {{ $grant->user->email }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" type="submit">Revoke</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <aside>
            <div class="panel">
                <div class="panel-head">
                    <h3>Invite a reader</h3>
                </div>

                <form method="POST" action="{{ route('dashboard.access.store', $book) }}">
                    @csrf
                    <div class="form-grid" style="grid-template-columns:1fr">
                        <label class="field">
                            <span>Reader email</span>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="reader@example.com">
                            <span class="hint">They must already have a PenakeTik account.</span>
                            @error('email') <span class="error-text">{{ $message }}</span> @enderror
                        </label>
                        <label class="field">
                            <span>Note <span class="hint">optional</span></span>
                            <input type="text" name="note" maxlength="190" value="{{ old('note') }}" placeholder="Backer, friend, reviewer…">
                        </label>
                    </div>
                    <button class="btn btn-accent btn-block mt-2" type="submit">Grant access</button>
                </form>
            </div>

            <div class="panel">
                <h3 style="font-size:1.05rem">Which chapters does this open?</h3>
                <p class="small muted mb-0">
                    Only chapters with the <strong>Limited free</strong> access level. Public previews stay public,
                    and paid chapters still require a verified purchase.
                </p>
            </div>
        </aside>
    </div>
@endsection
