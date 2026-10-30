<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'pen_name', 'email', 'role', 'password', 'bio', 'avatar_path', 'website'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function isReader(): bool
    {
        return $this->role === 'reader';
    }

    /**
     * Where this account belongs after logging in.
     */
    public function homeRoute(): string
    {
        return $this->isAuthor() ? route('dashboard.index') : route('reader.index');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest('id');
    }

    public function likedBooks()
    {
        return $this->belongsToMany(Book::class, 'book_likes')
            ->withTimestamps()
            ->latest('book_likes.created_at');
    }

    public function books()
    {
        return $this->hasMany(Book::class)->latest('id');
    }

    public function publishedBooks()
    {
        return $this->hasMany(Book::class)->where('status', 'published')->latest('published_at');
    }

    public function accessGrants()
    {
        return $this->hasMany(AccessGrant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function storeLinks()
    {
        return $this->hasMany(StoreLink::class)->orderBy('position');
    }

    public function displayName(): string
    {
        return $this->pen_name ?: $this->name;
    }

    public function initials(): string
    {
        return Str::of($this->displayName())
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : null;
    }
}
