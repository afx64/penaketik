@extends('layouts.dashboard')

@section('title', 'Author profile')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Public presence</span>
            <h1>Author profile</h1>
            <p>What readers see on your home page and author page.</p>
        </div>
        <a class="btn btn-ghost btn-sm" href="{{ route('authors.show', $user) }}">View public profile ↗</a>
    </div>

    <div class="editor-grid">
        <div>
            <form method="POST" action="{{ route('dashboard.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="panel">
                    <div class="panel-head">
                        <h2>About you</h2>
                    </div>

                    <div class="form-grid">
                        <label class="field">
                            <span>Legal / account name</span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="120">
                            @error('name') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>Pen name <span class="hint">shown to readers</span></span>
                            <input type="text" name="pen_name" value="{{ old('pen_name', $user->pen_name) }}" maxlength="120">
                        </label>

                        <label class="field">
                            <span>Website</span>
                            <input type="url" name="website" value="{{ old('website', $user->website) }}" maxlength="190" placeholder="https://">
                            @error('website') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>Avatar <span class="hint">square image works best</span></span>
                            <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp">
                            @error('avatar') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field field-full">
                            <span>Bio</span>
                            <textarea name="bio" rows="5" maxlength="4000" placeholder="Tell readers what you write, and what they can expect.">{{ old('bio', $user->bio) }}</textarea>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-accent" type="submit">Save profile</button>
                    </div>
                </div>
            </form>

            <div class="panel">
                <div class="panel-head">
                    <h2>Bookstores elsewhere</h2>
                    <span class="tiny muted">Shown as “also available on”</span>
                </div>

                @if ($links->isEmpty())
                    <p class="small muted">No external storefronts yet — Google Play Books, Kobo, Amazon, your own Gumroad page…</p>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Points to</th>
                                    <th>Link</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($links as $link)
                                    <tr>
                                        <td><strong class="small">{{ $link->displayLabel() }}</strong></td>
                                        <td class="small muted">{{ $link->book?->title ?? 'Whole storefront' }}</td>
                                        <td class="tiny"><a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">Open ↗</a></td>
                                        <td>
                                            <form method="POST" action="{{ route('dashboard.profile.links.destroy', $link) }}" data-confirm="Remove this storefront link?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="icon-btn danger" type="submit" title="Remove">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <aside>
            <div class="panel">
                <div class="dash-user" style="border:0;margin:0;padding:0 0 .75rem">
                    @if ($user->avatarUrl())
                        <img class="avatar avatar-sm" src="{{ $user->avatarUrl() }}" alt="">
                    @else
                        <span class="avatar avatar-sm">{{ $user->initials() }}</span>
                    @endif
                    <div class="who">
                        <strong>{{ $user->displayName() }}</strong>
                        <span>{{ $user->website ?: 'No website' }}</span>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h3>Add a storefront</h3>
                </div>

                <form method="POST" action="{{ route('dashboard.profile.links.store') }}">
                    @csrf
                    <div class="form-grid" style="grid-template-columns:1fr">
                        <label class="field">
                            <span>Platform</span>
                            <input type="text" name="platform" required maxlength="80" placeholder="Google Play Books">
                            @error('platform') <span class="error-text">{{ $message }}</span> @enderror
                        </label>
                        <label class="field">
                            <span>Label <span class="hint">optional</span></span>
                            <input type="text" name="label" maxlength="80" placeholder="eBook edition">
                        </label>
                        <label class="field">
                            <span>URL</span>
                            <input type="url" name="url" required maxlength="190" placeholder="https://">
                            @error('url') <span class="error-text">{{ $message }}</span> @enderror
                        </label>
                        <label class="field">
                            <span>Attach to book <span class="hint">optional</span></span>
                            <select name="book_id">
                                <option value="">Whole storefront</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <button class="btn btn-block mt-2" type="submit">Add link</button>
                </form>
            </div>
        </aside>
    </div>
@endsection
