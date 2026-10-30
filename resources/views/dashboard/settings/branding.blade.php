@extends('layouts.dashboard')

@section('title', 'Branding settings')

@php
    $logoLight = \App\Support\Settings::logoUrl('light');
    $logoDark = \App\Support\Settings::logoUrl('dark');
    $faviconUrl = \App\Support\Settings::faviconUrl();
    $currentTheme = \App\Support\Settings::themeMode();
    $currentFont = \App\Support\Settings::fontKey();
    $currentSample = \App\Support\Settings::fontSample();
@endphp

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Look and feel</span>
            <h1>Branding</h1>
            <p>Your logos, the light or dark scheme, and the typeface readers see across the site.</p>
        </div>
        <span class="badge badge-approved">Self-hosted fonts</span>
    </div>

    <div class="editor-grid">
        <form method="POST" action="{{ route('dashboard.settings.branding.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="panel">
                <div class="panel-head">
                    <h2>Logos &amp; favicon</h2>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <span>Light mode logo <span class="hint">shown on the light theme</span></span>

                        <div class="brand-asset">
                            <div class="brand-asset-preview">
                                @if ($logoLight)
                                    <img src="{{ $logoLight }}" alt="Current light mode logo">
                                @else
                                    <span class="empty">No logo yet</span>
                                @endif
                            </div>

                            <div class="brand-asset-body">
                                <input type="file" name="brand_logo_light" accept=".jpg,.jpeg,.png,.webp">
                                @if ($logoLight)
                                    <label class="checkbox">
                                        <input type="checkbox" name="remove_brand_logo_light" value="1">
                                        Remove
                                    </label>
                                @endif
                            </div>
                        </div>

                        @error('brand_logo_light') <span class="error-text">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <span>Dark mode logo <span class="hint">shown on the dark theme</span></span>

                        <div class="brand-asset">
                            <div class="brand-asset-preview is-dark">
                                @if ($logoDark)
                                    <img src="{{ $logoDark }}" alt="Current dark mode logo">
                                @else
                                    <span class="empty">No logo yet</span>
                                @endif
                            </div>

                            <div class="brand-asset-body">
                                <input type="file" name="brand_logo_dark" accept=".jpg,.jpeg,.png,.webp">
                                @if ($logoDark)
                                    <label class="checkbox">
                                        <input type="checkbox" name="remove_brand_logo_dark" value="1">
                                        Remove
                                    </label>
                                @endif
                            </div>
                        </div>

                        @error('brand_logo_dark') <span class="error-text">{{ $message }}</span> @enderror
                    </div>

                    <div class="field field-full">
                        <span>Favicon <span class="hint">square PNG or ICO up to 512 KB</span></span>

                        <div class="brand-asset">
                            <div class="brand-asset-preview" style="min-width:64px;max-width:72px">
                                @if ($faviconUrl)
                                    <img src="{{ $faviconUrl }}" alt="Current favicon" style="max-height:32px;max-width:32px">
                                @else
                                    <span class="empty">Default</span>
                                @endif
                            </div>

                            <div class="brand-asset-body">
                                <input type="file" name="brand_favicon" accept=".png,.ico,.jpg,.jpeg,.webp">
                                <small class="hint">Replaces the wordmark in the header when a logo is set. Leave empty to keep the current file.</small>
                                @if ($faviconUrl)
                                    <label class="checkbox">
                                        <input type="checkbox" name="remove_brand_favicon" value="1">
                                        Remove the current favicon
                                    </label>
                                @endif
                            </div>
                        </div>

                        @error('brand_favicon') <span class="error-text">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Colour scheme</h2>
                </div>

                <p class="small muted" style="margin-top:-0.35rem">
                    Applied to every page. Readers without JavaScript always get Light unless you pick Dark.
                </p>

                <div class="theme-grid">
                    @foreach ($themes as $key => $theme)
                        <label class="theme-card">
                            <input type="radio" name="theme_mode" value="{{ $key }}" @checked($currentTheme === $key)>
                            <span class="theme-swatch" aria-hidden="true">
                                @if ($key === 'system')
                                    <span style="background: linear-gradient(90deg, #f6f1e7 50%, #16120e 50%)"></span>
                                    <span style="background: linear-gradient(90deg, #a53f1e 50%, #e2743f 50%)"></span>
                                @else
                                    <span style="background: {{ $key === 'dark' ? '#16120e' : '#f6f1e7' }}"></span>
                                    <span style="background: {{ $key === 'dark' ? '#1e1914' : '#fffdf8' }}"></span>
                                    <span style="background: {{ $key === 'dark' ? '#e2743f' : '#a53f1e' }}"></span>
                                @endif
                            </span>
                            <span class="theme-name">{{ $theme['label'] }}</span>
                            <span class="theme-note">{{ $theme['note'] }}</span>
                        </label>
                    @endforeach
                </div>

                @error('theme_mode') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Typography</h2>
                </div>

                <p class="small muted" style="margin-top:-0.35rem">
                    Every family below is bundled with PenakeTik and served from your own domain, so no visitor
                    waits on Google Fonts. Only the family you pick is downloaded.
                </p>

                <div class="font-grid">
                    @foreach ($fonts as $key => $font)
                        <label class="font-card">
                            <input type="radio" name="font_family" value="{{ $key }}" @checked($currentFont === $key)>
                            <span class="font-sample" style="font-family: {{ $font['sample'] }}">Aa</span>
                            <span class="font-name">{{ $font['label'] }}</span>
                            <span class="font-note">{{ $font['note'] }}</span>
                        </label>
                    @endforeach
                </div>

                @error('font_family') <span class="error-text">{{ $message }}</span> @enderror

                <div class="form-actions">
                    <button class="btn btn-accent" type="submit">Save branding</button>
                    <a class="btn btn-ghost" href="{{ route('home') }}" target="_blank" rel="noopener">Preview the site ↗</a>
                </div>
            </div>
        </form>

        <aside>
            <div class="panel">
                <div class="panel-head">
                    <h3>Live preview</h3>
                </div>

                <div class="font-preview" id="font-preview" style="font-family: {{ $currentSample }}">
                    <p class="tiny muted" style="margin-bottom:0.5rem;letter-spacing:0.12em;text-transform:uppercase">The tide ledger</p>
                    <h3>A chapter heading</h3>
                    <p>Readers arrive for the story and stay for the voice. This is how a paragraph of your prose
                        will look with the typeface you choose.</p>
                    <p class="tiny muted mb-0">Chapter 3 · 12 min read · Free</p>
                </div>

                <p class="tiny muted mt-1 mb-0">Updates as you pick a font, before you save.</p>
            </div>

            <div class="panel">
                <h3 style="font-size:1.05rem">How the logos are used</h3>
                <ul class="small muted" style="padding-left:1.1rem;line-height:1.8">
                    <li>The light logo appears in light mode, the dark logo in dark mode.</li>
                    <li>Upload only one and it is used in both schemes.</li>
                    <li>A logo replaces the PenakeTik wordmark in the header.</li>
                </ul>
                <p class="tiny muted mt-1 mb-0">Served from <code>/storage/branding</code>.</p>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var preview = document.getElementById('font-preview');
            var stacks = @json(collect($fonts)->mapWithKeys(fn ($font) => [$font['label'] => $font['sample']]));

            document.querySelectorAll('input[name="font_family"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    if (!preview) {
                        return;
                    }

                    var label = input.parentElement.querySelector('.font-name').textContent.trim();

                    preview.style.fontFamily = stacks[label] || '';
                });
            });
        })();
    </script>
@endpush
