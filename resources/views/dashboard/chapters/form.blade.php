@extends('layouts.dashboard')

@section('title', ($mode === 'create' ? 'New chapter' : 'Edit chapter').' · '.$book->title)

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
@endpush

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">{{ $book->title }}</span>
            <h1>{{ $mode === 'create' ? 'New chapter' : $chapter->title }}</h1>
            <p>Write with headings, quotes, lists, links and images. Choose who gets to read it.</p>
        </div>
        <div class="flex gap-1 wrap">
            <a class="btn btn-ghost btn-sm" href="{{ route('dashboard.books.edit', $book) }}">Back to book</a>
            <a class="btn btn-ghost btn-sm" href="{{ route('books.show', $book) }}">View book ↗</a>
        </div>
    </div>

    <form method="POST"
          action="{{ $mode === 'create' ? route('dashboard.chapters.store', $book) : route('dashboard.chapters.update', [$book, $chapter]) }}">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <div class="editor-grid">
            <div>
                <div class="panel" style="padding:1.25rem">
                    <div class="form-grid">
                        <label class="field">
                            <span>Chapter title</span>
                            <input type="text" name="title" value="{{ old('title', $chapter->title) }}" required maxlength="180" placeholder="Chapter title">
                            @error('title') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>Who can read it?</span>
                            <select name="access" required>
                                @foreach (\App\Models\Chapter::ACCESS_LEVELS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('access', $chapter->access) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="hint">Public works only in the first {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} positions.</span>
                            @error('access') <span class="error-text">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <div class="editor-shell mt-2">
                    <div class="editor-toolbar-note">
                        Images upload to your own storage — nothing is embedded as a giant base64 blob.
                    </div>

                    <input type="hidden" name="content" id="content-input" value="{{ old('content', $chapter->content) }}">
                    <div id="editor" data-upload-url="{{ route('dashboard.chapters.image', $book) }}"></div>
                </div>

                <div class="panel mt-2">
                    <label class="checkbox">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $chapter->is_published))>
                        Published — readers can open this chapter
                    </label>

                    <div class="form-actions">
                        <button class="btn btn-accent" type="submit">{{ $mode === 'create' ? 'Save chapter' : 'Save changes' }}</button>
                        <a class="link-quiet" href="{{ route('dashboard.books.edit', $book) }}">Cancel</a>
                    </div>
                </div>
            </div>

            <aside>
                <div class="panel">
                    <h3 style="font-size:1.05rem">Position</h3>
                    <p class="small muted mb-0">
                        @if ($mode === 'create')
                            This chapter becomes <strong>#{{ $chapter->position }}</strong>.
                        @else
                            This chapter is <strong>#{{ $chapter->position }}</strong>.
                        @endif
                        Drag chapters on the book page to change the order.
                    </p>
                </div>

                <div class="panel">
                    <h3 style="font-size:1.05rem">Access reminder</h3>
                    <ul class="small muted" style="padding-left:1.1rem;line-height:1.8">
                        <li><strong>Public preview:</strong> no login, first {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} chapters only.</li>
                        <li><strong>Free:</strong> any signed-in reader.</li>
                        <li><strong>Limited free:</strong> only readers you invited.</li>
                        <li><strong>Paid:</strong> only readers whose transfer you approved.</li>
                    </ul>
                </div>

                @if ($mode === 'edit')
                    <form method="POST" action="{{ route('dashboard.chapters.destroy', [$book, $chapter]) }}" data-confirm="Delete chapter “{{ $chapter->title }}”?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm btn-block" type="submit">Delete this chapter</button>
                    </form>
                @endif
            </aside>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="{{ asset('js/editor.js') }}" defer></script>
@endpush
