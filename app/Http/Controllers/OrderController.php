<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Support\Notifier;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request, Book $book)
    {
        abort_unless($book->isPublished(), 404);
        abort_unless((float) $book->price > 0, 403, 'This book is not sold separately.');

        if (! Settings::get('bank_account_number')) {
            return back()->withErrors([
                'order' => 'The author has not finished setting up payments yet. Please check back later.',
            ]);
        }

        $user = $request->user();

        if ($book->orders()->where('user_id', $user->id)->where('status', 'paid')->exists()) {
            return redirect()
                ->route('books.show', $book)
                ->with('status', 'You already own this book. Happy reading.');
        }

        $pending = $book->orders()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($pending) {
            return redirect()->route('orders.show', $pending);
        }

        $order = $book->orders()->create([
            'user_id' => $user->id,
            'reference' => 'PNT-'.Str::upper(Str::random(8)),
            'status' => 'pending',
            'amount' => $book->price,
            'currency' => $book->currency,
        ]);

        Notifier::send(
            $book->user->email,
            'New order '.$order->reference.' for '.$book->title,
            $user->displayName().' placed an order for '.$book->title.' ('.$order->amountLabel().").\n"
            .'Review it in your dashboard: '.route('dashboard.orders.index')
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Order created. Transfer the amount, then upload your proof of payment.');
    }

    public function show(Request $request, Order $order)
    {
        Gate::authorize('view', $order);

        return view('orders.show', [
            'order' => $order->load('book.user', 'user'),
            'payment' => [
                'bank_name' => Settings::get('bank_name', ''),
                'bank_account_name' => Settings::get('bank_account_name', ''),
                'bank_account_number' => Settings::get('bank_account_number', ''),
                'instructions' => Settings::get('payment_instructions', ''),
            ],
        ]);
    }

    public function uploadProof(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, ['pending', 'rejected'], true), 403);

        $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $path = $request->file('proof')->store('proofs/'.$order->id, 'local');

        if ($order->proof_path) {
            Storage::disk('local')->delete($order->proof_path);
        }

        $order->update([
            'proof_path' => $path,
            'status' => 'pending',
            'note' => $request->input('note'),
        ]);

        Notifier::send(
            $order->book->user->email,
            'Payment proof uploaded for '.$order->reference,
            $order->user->displayName().' uploaded proof for '.$order->book->title.'.'."\n"
            .route('dashboard.orders.index')
        );

        return back()->with('status', 'Proof uploaded. The author will verify your payment.');
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending', 403);

        $order->update(['status' => 'cancelled']);

        return redirect()
            ->route('library.index')
            ->with('status', 'Order '.$order->reference.' cancelled.');
    }
}
