<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChapterController extends Controller
{
    use GeneratesSlugs;

    public function create(Book $book)
    {
        Gate::authorize('manage', $book);

        return view('dashboard.chapters.form', [
            'book' => $book,
            'chapter' => new Chapter([
                'position' => ((int) $book->chapters()->max('position')) + 1,
                'access' => $book->chapters()->count() < Book::PUBLIC_PREVIEW_LIMIT ? 'public' : 'free',
                'is_published' => true,
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request, Book $book)
    {
        Gate::authorize('manage', $book);

        $data = $this->validated($request);

        $position = ((int) $book->chapters()->max('position')) + 1;
        $note = null;

        if ($data['access'] === 'public' && $position > Book::PUBLIC_PREVIEW_LIMIT) {
            $data['access'] = 'free';
            $note = 'Only the first '.Book::PUBLIC_PREVIEW_LIMIT.' chapters can be public previews, so this chapter was saved as Free.';
        }

        $chapter = $book->chapters()->create([
            ...$data,
            'position' => $position,
            'slug' => $this->uniqueSlug(
                $data['title'],
                fn (string $slug) => $book->chapters()->where('slug', $slug)->exists(),
                'chapter'
            ),
        ]);

        return redirect()
            ->route('dashboard.chapters.edit', [$book, $chapter])
            ->with('status', $note ?? 'Chapter saved.');
    }

    public function edit(Book $book, Chapter $chapter)
    {
        Gate::authorize('manage', $book);
        abort_unless($chapter->book_id === $book->id, 404);

        return view('dashboard.chapters.form', [
            'book' => $book,
            'chapter' => $chapter,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Book $book, Chapter $chapter)
    {
        Gate::authorize('manage', $book);
        abort_unless($chapter->book_id === $book->id, 404);

        $data = $this->validated($request);
        $note = null;

        if ($data['access'] === 'public' && $chapter->position > Book::PUBLIC_PREVIEW_LIMIT) {
            $data['access'] = 'free';
            $note = 'Only the first '.Book::PUBLIC_PREVIEW_LIMIT.' chapters can be public previews, so this chapter was saved as Free.';
        }

        $chapter->update($data);

        return redirect()
            ->route('dashboard.chapters.edit', [$book, $chapter])
            ->with('status', $note ?? 'Chapter saved.');
    }

    public function destroy(Book $book, Chapter $chapter)
    {
        Gate::authorize('manage', $book);
        abort_unless($chapter->book_id === $book->id, 404);

        $chapter->delete();

        $book->chapters()->get()->each(function (Chapter $item, int $index) {
            if ($item->position !== $index + 1) {
                $item->update(['position' => $index + 1]);
            }
        });

        return redirect()
            ->route('dashboard.books.edit', $book)
            ->with('status', 'Chapter deleted.');
    }

    /**
     * Persist the order produced by drag and drop.
     */
    public function reorder(Request $request, Book $book)
    {
        Gate::authorize('manage', $book);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $chapters = $book->chapters()->get()->keyBy('id');

        if ($chapters->count() !== count($data['ids']) || array_diff($data['ids'], $chapters->keys()->all())) {
            return response()->json(['message' => 'That chapter list is out of date. Please reload.'], 422);
        }

        foreach ($data['ids'] as $index => $id) {
            $chapters[$id]->update(['position' => $index + 1]);
        }

        return response()->json(['message' => 'Chapter order saved.']);
    }

    /**
     * Image insert for the rich text editor.
     */
    public function uploadImage(Request $request, Book $book)
    {
        Gate::authorize('manage', $book);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $path = $request->file('image')->store('chapters/'.$book->id, 'public');

        return response()->json(['url' => asset('storage/'.$path)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'access' => ['required', 'in:'.implode(',', array_keys(Chapter::ACCESS_LEVELS))],
            'content' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['content'] = HtmlSanitizer::clean($data['content'] ?? '');
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
