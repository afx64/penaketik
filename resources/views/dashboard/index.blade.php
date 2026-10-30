@extends('layouts.dashboard')

@section('title', 'Overview')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Author studio</span>
            <h1>Welcome back, {{ auth()->user()->displayName() }}</h1>
            <p>Everything you publish, who is reading it, and what is waiting for your approval.</p>
        </div>
        <a class="btn btn-accent" href="{{ route('dashboard.books.create') }}">+ New book</a>
    </div>

    <div class="stat-grid">
        <div class="stat">
            <span>Books</span>
            <strong>{{ $stats['books'] }}</strong>
        </div>
        <div class="stat">
            <span>Chapters</span>
            <strong>{{ $stats['chapters'] }}</strong>
        </div>
        <div class="stat">
            <span>Invited readers</span>
            <strong>{{ $stats['grants'] }}</strong>
        </div>
        <div class="stat">
            <span>Pending orders</span>
            <strong>{{ $stats['pending_orders'] }}</strong>
        </div>
        <div class="stat">
            <span>Verified revenue</span>
            <strong>{{ ($site['currency'] ?? 'IDR') }} {{ number_format((float) $stats['revenue'], 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="editor-grid">
        <div>
            <div class="panel">
                <div class="panel-head">
                    <h2>Your books</h2>
                    <a class="link-quiet" href="{{ route('dashboard.books.index') }}">All books →</a>
                </div>

                @if ($books->isEmpty())
                    <div class="empty" style="padding:2rem 1rem">
                        <h3>No books yet</h3>
                        <p>Create a book, add chapters, choose what stays free, and publish.</p>
                        <a class="btn btn-sm mt-2" href="{{ route('dashboard.books.create') }}">Create your first book</a>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Status</th>
                                    <th>Chapters</th>
                                    <th>Invites</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($books as $book)
                                    <tr>
                                        <td><strong>{{ $book->title }}</strong></td>
                                        <td>
                                            <span class="badge {{ $book->isPublished() ? 'badge-public' : 'badge-draft' }}">
                                                {{ ucfirst($book->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $book->chapters_count }}</td>
                                        <td>{{ $book->grants_count }}</td>
                                        <td><a class="link-quiet" href="{{ route('dashboard.books.edit', $book) }}">Edit</a></td>
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
                <div class="panel-head">
                    <h3>Latest orders</h3>
                    <a class="link-quiet tiny" href="{{ route('dashboard.orders.index') }}">All →</a>
                </div>

                @if ($recentOrders->isEmpty())
                    <p class="small muted mb-0">No orders yet. Paid chapters show up here once a reader checks out.</p>
                @else
                    @foreach ($recentOrders as $order)
                        <div class="flex between items-center gap-1" style="padding:.55rem 0;border-bottom:1px solid var(--line)">
                            <div style="min-width:0">
                                <div class="small" style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $order->book->title }}</div>
                                <div class="tiny muted">{{ $order->user->displayName() }} · {{ $order->amountLabel() }}</div>
                            </div>
                            <span class="badge badge-{{ $order->status === 'paid' ? 'approved' : ($order->status === 'pending' ? 'pending' : 'rejected') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    @endforeach
                @endif

                <a class="btn btn-ghost btn-sm btn-block mt-3" href="{{ route('dashboard.orders.index') }}">Review orders</a>
            </div>

            <div class="panel">
                <h3 style="font-size:1.05rem">Finish setting up</h3>
                <div class="small muted" style="line-height:2">
                    <div>{{ $site['bank_account_number'] ? '✓' : '○' }} <a href="{{ route('dashboard.settings.payment') }}">Bank transfer details</a></div>
                    <div>{{ ($site['mail_mailer'] ?? 'log') !== 'log' ? '✓' : '○' }} <a href="{{ route('dashboard.settings.mail') }}">Mail server</a></div>
                    <div>{{ ($site['seo_site_description'] ?? '') !== '' ? '✓' : '○' }} <a href="{{ route('dashboard.settings.seo') }}">Site SEO</a></div>
                    <div>{{ auth()->user()->bio ? '✓' : '○' }} <a href="{{ route('dashboard.profile.edit') }}">Author bio &amp; avatar</a></div>
                </div>
            </div>
        </aside>
    </div>
@endsection
