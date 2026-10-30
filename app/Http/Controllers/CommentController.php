<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Book $book)
    {
        abort_unless($book->isPublished(), 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $book->comments()->create([
            'user_id' => $request->user()->id,
            'body' => trim($data['body']),
        ]);

        return back()
            ->with('status', 'Comment posted.')
            ->withFragment('comments');
    }
}
