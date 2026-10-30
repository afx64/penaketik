<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;

class LibraryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $books = Book::query()
            ->published()
            ->with('user')
            ->where(function ($query) use ($user) {
                $query
                    ->whereIn('id', $user->accessGrants()->select('book_id'))
                    ->orWhereIn('id', $user->orders()->where('status', 'paid')->select('book_id'));
            })
            ->get();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with('book')
            ->latest('id')
            ->get();

        return view('library.index', compact('books', 'orders'));
    }
}
