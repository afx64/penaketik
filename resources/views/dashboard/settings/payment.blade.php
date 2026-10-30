@extends('layouts.dashboard')

@section('title', 'Payment settings')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Manual verification</span>
            <h1>Payments</h1>
            <p>Readers transfer to your bank account and upload the receipt. You approve each order.</p>
        </div>
        <span class="badge {{ ($site['bank_account_number'] ?? '') ? 'badge-approved' : 'badge-pending' }}">
            {{ ($site['bank_account_number'] ?? '') ? 'Ready to sell' : 'Not configured' }}
        </span>
    </div>

    <div class="editor-grid">
        <form method="POST" action="{{ route('dashboard.settings.payment.update') }}">
            @csrf
            @method('PUT')

            <div class="panel">
                <div class="panel-head">
                    <h2>Bank transfer</h2>
                </div>

                <div class="form-grid">
                    <label class="field">
                        <span>Currency code</span>
                        <input type="text" name="currency" value="{{ old('currency', $site['currency'] ?? 'IDR') }}" required maxlength="8">
                        <span class="hint">Used for book prices and order totals.</span>
                        @error('currency') <span class="error-text">{{ $message }}</span> @enderror
                    </label>

                    <label class="field">
                        <span>Bank name</span>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $site['bank_name'] ?? '') }}" maxlength="120" placeholder="BCA, Mandiri, BNI…">
                    </label>

                    <label class="field">
                        <span>Account holder</span>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $site['bank_account_name'] ?? '') }}" maxlength="120">
                    </label>

                    <label class="field">
                        <span>Account number</span>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $site['bank_account_number'] ?? '') }}" maxlength="60">
                    </label>

                    <label class="field field-full">
                        <span>Instructions for readers</span>
                        <textarea name="payment_instructions" rows="5" maxlength="2000">{{ old('payment_instructions', $site['payment_instructions'] ?? '') }}</textarea>
                    </label>
                </div>

                <div class="form-actions">
                    <button class="btn btn-accent" type="submit">Save payment settings</button>
                </div>
            </div>
        </form>

        <aside>
            <div class="panel">
                <h3 style="font-size:1.05rem">How checkout works</h3>
                <ol class="small muted" style="padding-left:1.1rem;line-height:1.9">
                    <li>Reader opens a paid book and places an order.</li>
                    <li>They see your bank details and the order reference.</li>
                    <li>They upload the transfer receipt (JPG, PNG, WebP or PDF).</li>
                    <li>You open <strong>Orders</strong>, check your bank, approve.</li>
                    <li>The whole book unlocks in their library.</li>
                </ol>
            </div>

            <div class="panel">
                <h3 style="font-size:1.05rem">Reader-facing preview</h3>
                <table class="table small">
                    <tbody>
                        <tr><th>Bank</th><td>{{ ($site['bank_name'] ?? '') ?: '—' }}</td></tr>
                        <tr><th>Name</th><td>{{ ($site['bank_account_name'] ?? '') ?: '—' }}</td></tr>
                        <tr><th>Number</th><td>{{ ($site['bank_account_number'] ?? '') ?: '—' }}</td></tr>
                    </tbody>
                </table>
                <p class="tiny muted mt-1 mb-0">Orders can't be placed until an account number is saved.</p>
            </div>
        </aside>
    </div>
@endsection
