@extends('layouts.public')

@section('title', 'Order '.$order->reference.' · '.config('app.name'))
@section('meta_description', 'Payment instructions for your PenakeTik order.')

@section('content')
    <section class="shell section">
        <p class="small muted"><a href="{{ route('library.index') }}">My library</a> / Order {{ $order->reference }}</p>

        <div class="section-head">
            <div>
                <span class="eyebrow">Order {{ $order->reference }}</span>
                <h1 class="mb-0">{{ $order->book->title }}</h1>
                <p style="margin-top:.5rem">
                    {{ $order->amountLabel() }} · placed {{ $order->created_at->format('j M Y, H:i') }}
                    ·
                    <span class="badge badge-{{ $order->status === 'paid' ? 'approved' : ($order->status === 'rejected' ? 'rejected' : 'pending') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
            </div>
            <div class="flex gap-1 wrap">
                <a class="btn btn-ghost btn-sm" href="{{ route('books.show', $order->book) }}">Book page</a>
                @if ($order->status === 'paid')
                    <a class="btn btn-sm" href="{{ route('books.show', $order->book) }}">Start reading</a>
                @endif
            </div>
        </div>

        @if ($order->status === 'paid')
            <div class="panel">
                <h2 style="font-size:1.2rem">Payment verified</h2>
                <p class="mb-0">
                    Your payment was confirmed on {{ $order->approved_at?->format('j M Y, H:i') }}.
                    Every chapter of this book is unlocked for your account.
                </p>
            </div>
        @elseif ($order->status === 'cancelled')
            <div class="panel">
                <h2 style="font-size:1.2rem">Order cancelled</h2>
                <p class="mb-2">This order is closed. You can start a new one from the book page.</p>
                <a class="btn btn-ghost btn-sm" href="{{ route('books.show', $order->book) }}">Back to the book</a>
            </div>
        @else
            <div class="editor-grid">
                <div class="flex" style="flex-direction:column;gap:1.5rem">
                    <div class="panel">
                        <div class="panel-head">
                            <h2>1. Transfer the exact amount</h2>
                        </div>

                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width:40%">Amount</th>
                                    <td><strong>{{ $order->amountLabel() }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Bank</th>
                                    <td>{{ $payment['bank_name'] ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Account name</th>
                                    <td>{{ $payment['bank_account_name'] ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Account number</th>
                                    <td><strong>{{ $payment['bank_account_number'] ?: 'Not configured yet' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Reference</th>
                                    <td><strong>{{ $order->reference }}</strong></td>
                                </tr>
                            </tbody>
                        </table>

                        @if ($payment['instructions'])
                            <p class="small muted mt-2">{{ $payment['instructions'] }}</p>
                        @endif

                        @if ($order->status === 'rejected')
                            <div class="flash flash-error mb-0">
                                <div>
                                    <strong>Previous proof was rejected.</strong>
                                    {{ $order->note }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="panel">
                        <div class="panel-head">
                            <h2>2. Upload your proof of payment</h2>
                        </div>

                        <form method="POST" action="{{ route('orders.proof', $order) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-grid">
                                <label class="field field-full">
                                    <span>Transfer receipt <span class="hint">JPG, PNG, WebP or PDF · max 4 MB</span></span>
                                    <input type="file" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                    @error('proof') <span class="error-text">{{ $message }}</span> @enderror
                                </label>
                                <label class="field field-full">
                                    <span>Note to the author <span class="hint">optional</span></span>
                                    <input type="text" name="note" maxlength="500" value="{{ old('note') }}" placeholder="Sender name on the transfer, transfer time…">
                                </label>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-accent" type="submit">{{ $order->proof_path ? 'Replace proof' : 'Upload proof' }}</button>
                                @if ($order->proof_path)
                                    <span class="small muted">A proof is already attached.</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <aside>
                    <div class="panel">
                        <h3 style="font-size:1.05rem">What happens next</h3>
                        <ol class="small muted" style="padding-left:1.1rem;line-height:1.9">
                            <li>You transfer and upload the receipt.</li>
                            <li>The author checks their bank statement.</li>
                            <li>They approve the order from the dashboard.</li>
                            <li>The whole book unlocks in your library.</li>
                        </ol>
                    </div>

                    @if ($order->status === 'pending')
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-2" data-confirm="Cancel this order?">
                            @csrf
                            <button class="btn btn-danger btn-sm btn-block" type="submit">Cancel order</button>
                        </form>
                    @endif
                </aside>
            </div>
        @endif
    </section>
@endsection
