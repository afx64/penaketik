<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Http\Request;

class BookstoreController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::query()
            ->published()
            ->with('user')
            ->withCount('chapters')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';

                $query->where(fn ($inner) => $inner
                    ->where('title', 'like', $term)
                    ->orWhere('synopsis', 'like', $term)
                    ->orWhereHas('user', fn ($author) => $author
                        ->where('name', 'like', $term)
                        ->orWhere('pen_name', 'like', $term)));
            })
            ->when($request->filled('genre'), fn ($query) => $query->where('genre', $request->string('genre')))
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $genres = Book::query()
            ->published()
            ->whereNotNull('genre')
            ->distinct()
            ->orderBy('genre')
            ->pluck('genre');

        return view('books.index', compact('books', 'genres'));
    }

    public function show(Book $book)
    {
        $isOwner = auth()->check() && auth()->id() === $book->user_id;

        abort_unless($book->isPublished() || $isOwner, 404);

        $user = auth()->user();
        $pass = $book->readerPass($user);

        $chapters = $book->chapters()
            ->when(! $isOwner, fn ($query) => $query->where('is_published', true))
            ->get();

        return view('books.show', [
            'book' => $book->load('user'),
            'chapters' => $chapters,
            'pass' => $pass,
            'isOwner' => $isOwner,
            'order' => $user ? $book->orders()->where('user_id', $user->id)->latest('id')->first() : null,
            'storeLinks' => $book->externalLinks(),
            'comments' => $book->comments()->with('user')->latest('id')->take(50)->get(),
            'commentCount' => $book->comments()->count(),
            'liked' => $user ? $book->isLikedBy($user) : false,
            'likeCount' => $book->likes()->count(),
        ]);
    }

    public function read(Book $book, Chapter $chapter)
    {
        abort_unless($chapter->book_id === $book->id, 404);

        $isOwner = auth()->check() && auth()->id() === $book->user_id;

        abort_unless($book->isPublished() || $isOwner, 404);
        abort_unless($chapter->is_published || $isOwner, 404);

        $user = auth()->user();
        $pass = $book->readerPass($user);

        if (! $chapter->readableWith($pass)) {
            return response()
                ->view('books.locked', [
                    'book' => $book->load('user'),
                    'chapter' => $chapter,
                    'reason' => $chapter->lockReason($pass),
                    'chapters' => $book->chapters()
                        ->when(! $isOwner, fn ($query) => $query->where('is_published', true))
                        ->get(),
                    'pass' => $pass,
                ])
                ->setStatusCode(403);
        }

        $siblings = $book->chapters()->where('is_published', true)->get();
        $index = $siblings->search(fn (Chapter $item) => $item->id === $chapter->id);

        return view('books.read', [
            'book' => $book->load('user'),
            'chapter' => $chapter,
            'chapters' => $siblings,
            'pass' => $pass,
            'isOwner' => $isOwner,
            'previous' => $index !== false && $index > 0 ? $siblings[$index - 1] : null,
            'next' => $index !== false && $index < $siblings->count() - 1 ? $siblings[$index + 1] : null,
        ]);
    }
}
