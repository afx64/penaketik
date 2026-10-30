<?php

namespace App\Http\Controllers;

use App\Models\User;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = User::query()
            ->whereHas('publishedBooks')
            ->withCount('publishedBooks')
            ->orderBy('name')
            ->get();

        return view('authors.index', compact('authors'));
    }

    public function show(User $user)
    {
        $books = $user->publishedBooks()->withCount('chapters')->get();

        abort_if($books->isEmpty() && auth()->id() !== $user->id, 404);

        return view('authors.show', [
            'author' => $user,
            'books' => $books,
            'storeLinks' => $user->storeLinks()->whereNull('book_id')->get(),
        ]);
    }
}
