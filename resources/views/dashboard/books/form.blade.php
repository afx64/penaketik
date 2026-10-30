@extends('layouts.dashboard')

@section('title', $mode === 'create' ? 'New book' : 'Edit '.$book->title)

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">{{ $mode === 'create' ? 'New book' : 'Editing' }}</span>
            <h1>{{ $mode === 'create' ? 'Add a new book' : $book->title }}</h1>
            <p>{{ $mode === 'create' ? 'The basics first. Chapters come next.' : 'Update the details, reorder chapters by dragging, and publish when ready.' }}</p>
        </div>
        <div class="flex gap-1 wrap">
            @if ($mode === 'edit')
                <a class="btn btn-ghost btn-sm" href="{{ route('dashboard.chapters.create', $book) }}">+ Chapter</a>
                <a class="btn btn-ghost btn-sm" href="{{ route('dashboard.access.index', $book) }}">Access</a>
                <a class="btn btn-ghost btn-sm" href="{{ route('books.show', $book) }}">View page ↗</a>
            @endif
        </div>
    </div>

    <div class="editor-grid">
        <div>
            <form method="POST"
                  action="{{ $mode === 'create' ? route('dashboard.books.store') : route('dashboard.books.update', $book) }}"
                  enctype="multipart/form-data">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="panel">
                    <div class="panel-head">
                        <h2>Book details</h2>
                    </div>

                    <div class="form-grid">
                        <label class="field field-full">
                            <span>Title</span>
                            <input type="text" name="title" value="{{ old('title', $book->title) }}" required maxlength="180">
                            @error('title') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>Subtitle <span class="hint">optional</span></span>
                            <input type="text" name="subtitle" value="{{ old('subtitle', $book->subtitle) }}" maxlength="180">
                        </label>

                        <label class="field">
                            <span>Genre</span>
                            <input type="text" name="genre" value="{{ old('genre', $book->genre) }}" maxlength="80" placeholder="Fantasy, memoir, sci-fi…">
                        </label>

                        <label class="field">
                            <span>Language</span>
                            <input type="text" name="language" value="{{ old('language', $book->language ?: 'id') }}" maxlength="12" required>
                        </label>

                        <label class="field">
                            <span>Full book price <span class="hint">0 = free</span></span>
                            <input type="number" name="price" value="{{ old('price', $book->price) }}" min="0" step="0.01">
                            <span class="hint">Currency is set in Payment settings (currently {{ $site['currency'] ?? 'IDR' }}).</span>
                            @error('price') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field field-full">
                            <span>Synopsis</span>
                            <textarea name="synopsis" rows="6" required maxlength="20000">{{ old('synopsis', $book->synopsis) }}</textarea>
                            @error('synopsis') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>Cover image <span class="hint">JPG, PNG or WebP · portrait works best</span></span>
                            <input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp">
                            @if ($book->coverUrl())
                                <span class="hint">Uploading a new cover replaces the current one.</span>
                            @endif
                            @error('cover') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <div class="field">
                            <span>Visibility</span>
                            <label class="checkbox"><input type="radio" name="status" value="draft" @checked(old('status', $book->status) === 'draft')> Draft — hidden from the bookstore</label>
                            <label class="checkbox"><input type="radio" name="status" value="published" @checked(old('status', $book->status) === 'published')> Published — visible to everyone</label>
                            @error('status') <span class="error-text">{{ $message }}</span> @enderror
                        </div>

                        <label class="checkbox field-full">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $book->is_featured))>
                            Feature this book on the home page
                        </label>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <h2>SEO</h2>
                        <span class="tiny muted">Overrides the site defaults for this book</span>
                    </div>

                    <div class="form-grid">
                        <label class="field field-full">
                            <span>Meta title <span class="hint">defaults to the book title</span></span>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $book->meta_title) }}" maxlength="180">
                        </label>
                        <label class="field field-full">
                            <span>Meta description <span class="hint">defaults to the synopsis</span></span>
                            <textarea name="meta_description" rows="3" maxlength="500">{{ old('meta_description', $book->meta_description) }}</textarea>
                        </label>
                        <label class="field field-full">
                            <span>Keywords</span>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $book->meta_keywords) }}" maxlength="255" placeholder="novel, serial, indonesian fiction">
                        </label>
                    </div>
                </div>

                <div class="form-actions" style="border:0;padding-top:0">
                    <button class="btn btn-accent" type="submit">{{ $mode === 'create' ? 'Create book' : 'Save changes' }}</button>
                    <a class="link-quiet" href="{{ route('dashboard.books.index') }}">Back to books</a>
                </div>
            </form>
        </div>

        @if ($mode === 'edit')
            <aside>
                <div class="panel">
                    <div class="panel-head">
                        <h3>Chapters</h3>
                        <a class="link-quiet tiny" href="{{ route('dashboard.chapters.create', $book) }}">+ Add</a>
                    </div>

                    @if ($book->chapters->isEmpty())
                        <p class="small muted">No chapters yet. Add the first one — it becomes a public preview automatically.</p>
                        <a class="btn btn-sm btn-block" href="{{ route('dashboard.chapters.create', $book) }}">Write chapter 1</a>
                    @else
                        <p class="tiny muted">Drag to reorder. Only the first {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} positions can be public previews.</p>

                        <ul class="chapter-manage-list" id="chapter-list"
                            data-reorder-url="{{ route('dashboard.chapters.reorder', $book) }}">
                            @foreach ($book->chapters as $chapter)
                                <li class="chapter-manage-item" data-chapter-id="{{ $chapter->id }}">
                                    <span class="drag-handle" title="Drag to reorder" aria-hidden="true">⠿</span>
                                    <span class="chapter-pos">{{ $chapter->position }}</span>
                                    <span class="chapter-name" title="{{ $chapter->title }}">
                                        {{ $chapter->title }}
                                        @if (! $chapter->is_published)
                                            <span class="badge badge-draft">hidden</span>
                                        @endif
                                    </span>
                                    <span class="badge badge-{{ $chapter->access }}">{{ $chapter->accessLabel() }}</span>
                                    <span class="chapter-actions">
                                        <a class="icon-btn" href="{{ route('dashboard.chapters.edit', [$book, $chapter]) }}" title="Edit chapter">✎</a>
                                        <form method="POST" action="{{ route('dashboard.chapters.destroy', [$book, $chapter]) }}" data-confirm="Delete chapter “{{ $chapter->title }}”?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-btn danger" type="submit" title="Delete chapter">✕</button>
                                        </form>
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        <a class="btn btn-ghost btn-sm btn-block mt-2" href="{{ route('dashboard.chapters.create', $book) }}">+ New chapter</a>
                    @endif
                </div>

                <div class="panel">
                    <h3 style="font-size:1.05rem">Access rule</h3>
                    <p class="small muted mb-2">
                        Chapters marked <strong>Public</strong> are readable by anyone in the first
                        {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} slots. Free chapters need a login, limited-free
                        chapters need an invitation, and paid chapters unlock after a verified bank transfer.
                    </p>
                    <a class="btn btn-ghost btn-sm btn-block" href="{{ route('dashboard.access.index', $book) }}">Invite readers</a>
                </div>
            </aside>
        @else
            <aside>
                <div class="panel">
                    <h3 style="font-size:1.05rem">How access works</h3>
                    <ul class="small muted" style="padding-left:1.1rem;line-height:1.8">
                        <li><strong>Public preview:</strong> first {{ \App\Models\Book::PUBLIC_PREVIEW_LIMIT }} chapters, no account needed.</li>
                        <li><strong>Free:</strong> any registered reader.</li>
                        <li><strong>Limited free:</strong> readers you invite by email.</li>
                        <li><strong>Paid:</strong> readers whose transfer you approve.</li>
                    </ul>
                </div>
            </aside>
        @endif
    </div>
@endsection

@if ($mode === 'edit')
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script src="{{ asset('js/book-editor.js') }}" defer></script>
    @endpush
@endif
