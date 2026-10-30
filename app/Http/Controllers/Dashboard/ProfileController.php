<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StoreLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('dashboard.profile', [
            'user' => $user,
            'links' => $user->storeLinks()->with('book')->get(),
            'books' => $user->books()->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'pen_name' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:4000'],
            'website' => ['nullable', 'url', 'max:190'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($data['avatar']);

        $user->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function storeLink(Request $request)
    {
        $data = $request->validate([
            'platform' => ['required', 'string', 'max:80'],
            'label' => ['nullable', 'string', 'max:80'],
            'url' => ['required', 'url', 'max:190'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
        ]);

        if (! empty($data['book_id'])) {
            abort_unless(
                auth()->user()->books()->whereKey($data['book_id'])->exists(),
                403
            );
        }

        auth()->user()->storeLinks()->create([
            ...$data,
            'position' => ((int) auth()->user()->storeLinks()->max('position')) + 1,
        ]);

        return back()->with('status', 'Storefront link added.');
    }

    public function destroyLink(StoreLink $storeLink)
    {
        abort_unless($storeLink->user_id === auth()->id(), 403);

        $storeLink->delete();

        return back()->with('status', 'Storefront link removed.');
    }
}
