@extends('layouts.dashboard')

@section('title', 'Orders')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Payments</span>
            <h1>Orders</h1>
            <p>Check the transfer, then approve to unlock the book for that reader.</p>
        </div>
    </div>

    <div class="flex gap-1 wrap mb-2">
        <a class="btn btn-sm {{ request()->filled('status') ? 'btn-ghost' : '' }}" href="{{ route('dashboard.orders.index') }}">
            All
        </a>
        @foreach (['pending' => 'Pending', 'paid' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $value => $label)
            <a class="btn btn-sm {{ request('status') === $value ? '' : 'btn-ghost' }}"
               href="{{ route('dashboard.orders.index', ['status' => $value]) }}">
                {{ $label }}
                <span class="tiny">({{ $counts[$value] ?? 0 }})</span>
            </a>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <div class="empty">
            <h3>No orders here</h3>
            <p>When a reader buys a paid book, the order lands here with their proof of transfer.</p>
        </div>
    @else
        <div class="panel" style="padding:.5rem">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Reader</th>
                            <th>Book</th>
                            <th>Amount</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="tiny">
                                    {{ $order->reference }}
                                    <div class="muted">{{ $order->created_at->format('j M Y') }}</div>
                                </td>
                                <td>
                                    <strong class="small">{{ $order->user->displayName() }}</strong>
                                    <div class="tiny muted">{{ $order->user->email }}</div>
                                </td>
                                <td class="small">{{ $order->book->title }}</td>
                                <td class="small">{{ $order->amountLabel() }}</td>
                                <td class="small">
                                    @if ($order->proof_path)
                                        <a class="link-quiet" href="{{ route('dashboard.orders.proof', $order) }}">Download</a>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $order->status === 'paid' ? 'approved' : ($order->status === 'pending' ? 'pending' : 'rejected') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    @if ($order->note)
                                        <div class="tiny muted" style="max-width:15rem">{{ \Illuminate\Support\Str::limit($order->note, 60) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($order->status === 'pending')
                                        <div class="flex gap-1 wrap">
                                            <form method="POST" action="{{ route('dashboard.orders.approve', $order) }}" data-confirm="Approve {{ $order->amountLabel() }} from {{ $order->user->displayName() }}?">
                                                @csrf
                                                <button class="btn btn-sm" type="submit">Approve</button>
                                            </form>

                                            <form method="POST" action="{{ route('dashboard.orders.reject', $order) }}" class="flex gap-1">
                                                @csrf
                                                <input type="text" name="note" placeholder="Reason" maxlength="500" style="width:110px;padding:.3rem .45rem;font-size:.75rem">
                                                <button class="btn btn-danger btn-sm" type="submit">Reject</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="tiny muted">{{ $order->approved_at?->format('j M Y') ?? 'Closed' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $orders->links() }}
    @endif
@endsection
