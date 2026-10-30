<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccessGrantController extends Controller
{
    public function index(Book $book)
    {
        Gate::authorize('manage', $book);

        return view('dashboard.books.access', [
            'book' => $book,
            'grants' => $book->grants()->with('user')->latest('id')->get(),
        ]);
    }

    public function store(Request $request, Book $book)
    {
        Gate::authorize('manage', $book);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'note' => ['nullable', 'string', 'max:190'],
        ]);

        $reader = User::where('email', $data['email'])->first();

        if (! $reader) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'No reader has registered with that email yet. Ask them to create an account first.']);
        }

        if ($reader->id === $book->user_id) {
            return back()->withErrors(['email' => 'You already have access to your own book.']);
        }

        $book->grants()->updateOrCreate(
            ['user_id' => $reader->id],
            ['note' => $data['note'] ?? null, 'granted_at' => now()]
        );

        Notifier::send(
            $reader->email,
            'You now have access to '.$book->title,
            'Hello '.$reader->displayName()."\n\n"
            .$book->user->displayName().' granted you limited-free access to "'.$book->title.'".'."\n"
            .route('books.show', $book)
        );

        return back()->with('status', $reader->displayName().' can now read the limited-free chapters.');
    }

    public function destroy(Book $book, User $user)
    {
        Gate::authorize('manage', $book);

        $book->grants()->where('user_id', $user->id)->delete();

        return back()->with('status', 'Access revoked for '.$user->displayName().'.');
    }
}
