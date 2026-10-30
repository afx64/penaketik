<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $bookIds = auth()->user()->books()->pluck('id');

        $orders = Order::query()
            ->whereIn('book_id', $bookIds)
            ->with('book', 'user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.orders.index', [
            'orders' => $orders,
            'counts' => Order::whereIn('book_id', $bookIds)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function approve(Request $request, Order $order)
    {
        Gate::authorize('approve', $order);

        if ($order->status !== 'pending') {
            return back()->withErrors(['order' => 'Only pending orders can be approved.']);
        }

        $order->update(['status' => 'paid', 'approved_at' => now()]);

        Notifier::send(
            $order->user->email,
            'Payment verified for '.$order->book->title,
            'Hello '.$order->user->displayName()."\n\n"
            .'Your payment for "'.$order->book->title.'" is verified. The full book is now unlocked in your library.'."\n"
            .route('library.index')
        );

        return back()->with('status', 'Order '.$order->reference.' approved.');
    }

    public function reject(Request $request, Order $order)
    {
        Gate::authorize('approve', $order);

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $order->update([
            'status' => 'rejected',
            'note' => $data['note'] ?? 'The transfer could not be verified.',
        ]);

        Notifier::send(
            $order->user->email,
            'We could not verify your payment for '.$order->book->title,
            'Hello '.$order->user->displayName()."\n\n".$order->note."\n"
            .'You can upload a new proof of payment here: '.route('orders.show', $order)
        );

        return back()->with('status', 'Order '.$order->reference.' rejected.');
    }

    public function proof(Request $request, Order $order)
    {
        Gate::authorize('view', $order);

        abort_unless($order->proof_path && Storage::disk('local')->exists($order->proof_path), 404);

        return Storage::disk('local')->download($order->proof_path);
    }
}
