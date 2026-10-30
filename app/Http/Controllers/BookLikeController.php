<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLike;
use Illuminate\Http\Request;

class BookLikeController extends Controller
{
    public function toggle(Request $request, Book $book)
    {
        abort_unless($book->isPublished(), 404);

        $existing = BookLike::query()
            ->where('book_id', $book->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', 'Removed from your liked books.');
        }

        BookLike::create([
            'book_id' => $book->id,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('status', $book->title.' added to your liked books.');
    }
}
