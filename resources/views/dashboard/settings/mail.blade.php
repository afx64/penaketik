@extends('layouts.dashboard')

@section('title', 'Mail settings')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Delivery</span>
            <h1>Mail</h1>
            <p>Order notifications and invitations are sent with these settings — no .env edit needed.</p>
        </div>
        <span class="badge {{ ($site['mail_mailer'] ?? 'log') !== 'log' ? 'badge-approved' : 'badge-pending' }}">
            {{ ($site['mail_mailer'] ?? 'log') === 'log' ? 'Log only' : 'SMTP configured' }}
        </span>
    </div>

    <div class="editor-grid">
        <div>
            <form method="POST" action="{{ route('dashboard.settings.mail.update') }}">
                @csrf
                @method('PUT')

                <div class="panel">
                    <div class="panel-head">
                        <h2>Server</h2>
                    </div>

                    <div class="form-grid">
                        <label class="field">
                            <span>Mailer</span>
                            <select name="mail_mailer" required>
                                <option value="log" @selected(old('mail_mailer', $site['mail_mailer'] ?? 'log') === 'log')>Log only (development)</option>
                                <option value="smtp" @selected(old('mail_mailer', $site['mail_mailer'] ?? 'log') === 'smtp')>SMTP</option>
                            </select>
                            <span class="hint">“Log only” writes messages to storage/logs instead of sending.</span>
                        </label>

                        <label class="field">
                            <span>Encryption</span>
                            <select name="mail_encryption">
                                @foreach (['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'None'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('mail_encryption', $site['mail_encryption'] ?? 'tls') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field">
                            <span>SMTP host</span>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $site['mail_host'] ?? '') }}" placeholder="smtp.mailprovider.com">
                            @error('mail_host') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>Port</span>
                            <input type="number" name="mail_port" value="{{ old('mail_port', $site['mail_port'] ?? 587) }}" min="1" max="65535">
                            @error('mail_port') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>Username</span>
                            <input type="text" name="mail_username" value="{{ old('mail_username', $site['mail_username'] ?? '') }}" autocomplete="off">
                        </label>

                        <label class="field">
                            <span>Password</span>
                            <input type="password" name="mail_password" autocomplete="new-password" placeholder="{{ ($site['mail_password'] ?? '') ? '•••••••• (saved)' : '' }}">
                            <span class="hint">Leave blank to keep the saved password.</span>
                        </label>

                        <label class="field">
                            <span>From address</span>
                            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $site['mail_from_address'] ?? '') }}" placeholder="hello@yourdomain.com">
                            @error('mail_from_address') <span class="error-text">{{ $message }}</span> @enderror
                        </label>

                        <label class="field">
                            <span>From name</span>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $site['mail_from_name'] ?? '') }}" placeholder="{{ config('app.name') }}">
                        </label>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-accent" type="submit">Save mail settings</button>
                    </div>
                </div>
            </form>

            <div class="panel">
                <div class="panel-head">
                    <h2>Send a test</h2>
                </div>
                <p class="small muted">Saves nothing — it uses the settings already saved above.</p>

                <form method="POST" action="{{ route('dashboard.settings.mail.test') }}" class="flex gap-1 wrap items-center">
                    @csrf
                    <input type="email" name="to" value="{{ old('to', auth()->user()->email) }}" required style="max-width:300px">
                    <button class="btn btn-ghost" type="submit">Send test email</button>
                </form>
                @error('mail') <p class="error-text mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <aside>
            <div class="panel">
                <h3 style="font-size:1.05rem">Common providers</h3>
                <ul class="small muted" style="padding-left:1.1rem;line-height:1.8">
                    <li><strong>Gmail:</strong> smtp.gmail.com, port 587, TLS, app password required.</li>
                    <li><strong>Mailgun:</strong> smtp.mailgun.org, 587, TLS.</li>
                    <li><strong>Brevo:</strong> smtp-relay.brevo.com, 587, TLS.</li>
                    <li><strong>Zoho:</strong> smtp.zoho.com, 465, SSL.</li>
                </ul>
            </div>

            <div class="panel">
                <h3 style="font-size:1.05rem">What gets sent</h3>
                <ul class="small muted" style="padding-left:1.1rem;line-height:1.8">
                    <li>New order notice to the author.</li>
                    <li>Proof upload notice to the author.</li>
                    <li>Access invitation to the reader.</li>
                    <li>Approval or rejection to the buyer.</li>
                </ul>
                <p class="tiny muted mb-0">If sending fails, the action still succeeds — the failure is logged.</p>
            </div>
        </aside>
    </div>
@endsection
