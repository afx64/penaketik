<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * The site turns any signed-up author into a storefront. The home page
     * features the founding author plus the newest books on the platform.
     */
    public function index()
    {
        $author = User::query()
            ->whereHas('publishedBooks')
            ->oldest('id')
            ->first()
            ?? User::query()->oldest('id')->first();

        $books = Book::query()
            ->published()
            ->with('user')
            ->withCount('chapters')
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->take(6)
            ->get();

        $recentChapters = Chapter::query()
            ->where('is_published', true)
            ->whereHas('book', fn ($query) => $query->published())
            ->with('book.user')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('home', [
            'author' => $author,
            'books' => $books,
            'recentChapters' => $recentChapters,
            'storeLinks' => $author?->storeLinks()->whereNull('book_id')->get() ?? collect(),
        ]);
    }
}
