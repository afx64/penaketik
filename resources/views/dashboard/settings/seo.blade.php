@extends('layouts.dashboard')

@section('title', 'SEO settings')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Search engines</span>
            <h1>SEO</h1>
            <p>Defaults for the site. Each book can override its own title, description and keywords.</p>
        </div>
    </div>

    <div class="editor-grid">
        <form method="POST" action="{{ route('dashboard.settings.seo.update') }}">
            @csrf
            @method('PUT')

            <div class="panel">
                <div class="panel-head">
                    <h2>Site metadata</h2>
                </div>

                <div class="form-grid">
                    <label class="field field-full">
                        <span>Site tagline <span class="hint">shown in the footer and home page</span></span>
                        <input type="text" name="site_tagline" maxlength="180" value="{{ old('site_tagline', $site['site_tagline'] ?? '') }}">
                    </label>

                    <label class="field field-full">
                        <span>Default meta title</span>
                        <input type="text" name="seo_site_title" maxlength="180" value="{{ old('seo_site_title', $site['seo_site_title'] ?? '') }}" placeholder="{{ config('app.name') }}">
                    </label>

                    <label class="field field-full">
                        <span>Default meta description <span class="hint">aim for 150–160 characters</span></span>
                        <textarea name="seo_site_description" rows="3" maxlength="500">{{ old('seo_site_description', $site['seo_site_description'] ?? '') }}</textarea>
                    </label>

                    <label class="field field-full">
                        <span>Default keywords</span>
                        <input type="text" name="seo_site_keywords" maxlength="255" value="{{ old('seo_site_keywords', $site['seo_site_keywords'] ?? '') }}">
                    </label>

                    <label class="field field-full">
                        <span>Google site verification code</span>
                        <input type="text" name="seo_google_verification" maxlength="190" value="{{ old('seo_google_verification', $site['seo_google_verification'] ?? '') }}" placeholder="content value only">
                    </label>
                </div>

                <div class="form-actions">
                    <button class="btn btn-accent" type="submit">Save SEO settings</button>
                </div>
            </div>
        </form>

        <aside>
            <div class="panel">
                <h3 style="font-size:1.05rem">What is automatic</h3>
                <ul class="small muted" style="padding-left:1.1rem;line-height:1.8">
                    <li>Canonical URL on every page.</li>
                    <li>Open Graph and Twitter card tags.</li>
                    <li>Book cover used as the social image.</li>
                    <li>Locked chapters are marked <code>noarchive, nosnippet</code>.</li>
                    <li>Author pages get their own descriptions.</li>
                </ul>
            </div>

            <div class="panel">
                <h3 style="font-size:1.05rem">Search preview</h3>
                <p class="small" style="color:var(--blue);margin-bottom:.2rem">
                    {{ ($site['seo_site_title'] ?? '') ?: config('app.name') }}
                </p>
                <p class="tiny muted mb-0">https://your-domain.test</p>
                <p class="small muted mb-0">{{ ($site['seo_site_description'] ?? '') ?: 'Your default description appears here.' }}</p>
            </div>
        </aside>
    </div>
@endsection
