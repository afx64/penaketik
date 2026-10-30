<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    use GeneratesSlugs;

    public function index()
    {
        return view('dashboard.books.index', [
            'books' => auth()->user()->books()
                ->withCount(['chapters', 'grants'])
                ->latest('id')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('dashboard.books.form', [
            'book' => new Book([
                'status' => 'draft',
                'language' => 'id',
                'currency' => Settings::get('currency', 'IDR'),
                'price' => 0,
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug(
            $data['title'],
            fn (string $slug) => Book::where('slug', $slug)->exists(),
            'book'
        );

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book = Book::create($data);

        return redirect()
            ->route('dashboard.books.edit', $book)
            ->with('status', 'Book created. Add your chapters next.');
    }

    public function edit(Book $book)
    {
        Gate::authorize('update', $book);

        return view('dashboard.books.form', [
            'book' => $book->loadCount('chapters'),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Book $book)
    {
        Gate::authorize('update', $book);

        $data = $this->validated($request);

        if ($data['status'] === 'published' && ! $book->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover')) {
            if ($book->cover_path) {
                Storage::disk('public')->delete($book->cover_path);
            }

            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);

        return redirect()
            ->route('dashboard.books.edit', $book)
            ->with('status', 'Book details saved.');
    }

    public function destroy(Book $book)
    {
        Gate::authorize('delete', $book);

        if ($book->cover_path) {
            Storage::disk('public')->delete($book->cover_path);
        }

        $book->delete();

        return redirect()
            ->route('dashboard.books.index')
            ->with('status', 'Book deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'genre' => ['nullable', 'string', 'max:80'],
            'language' => ['required', 'string', 'max:12'],
            'synopsis' => ['required', 'string', 'max:20000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['price'] = $data['price'] ?? 0;
        $data['is_featured'] = $request->boolean('is_featured');
        unset($data['cover']);

        return $data;
    }
}
