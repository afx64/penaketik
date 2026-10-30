<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Comment;
use App\Models\Order;

class ReaderController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $purchased = Book::query()
            ->published()
            ->with('user')
            ->whereIn('id', $user->orders()->where('status', 'paid')->select('book_id'))
            ->get();

        $invited = Book::query()
            ->published()
            ->with('user')
            ->whereIn('id', $user->accessGrants()->select('book_id'))
            ->get();

        $liked = $user->likedBooks()->with('user')->get();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with('book')
            ->latest('id')
            ->take(5)
            ->get();

        $comments = Comment::query()
            ->where('user_id', $user->id)
            ->with('book')
            ->latest('id')
            ->take(10)
            ->get();

        return view('reader.index', [
            'purchased' => $purchased,
            'invited' => $invited,
            'liked' => $liked,
            'orders' => $orders,
            'comments' => $comments,
            'commentCount' => $user->comments()->count(),
        ]);
    }
}
