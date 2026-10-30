<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'title', 'slug', 'subtitle', 'genre', 'language', 'synopsis',
    'cover_path', 'price', 'currency', 'status', 'is_featured',
    'meta_title', 'meta_description', 'meta_keywords', 'published_at',
])]
class Book extends Model
{
    /** Number of chapters every visitor may read before any gating applies. */
    public const PUBLIC_PREVIEW_LIMIT = 3;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function grants()
    {
        return $this->hasMany(AccessGrant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(BookLike::class);
    }

    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'book_likes')->withTimestamps();
    }

    public function isLikedBy(?User $user): bool
    {
        return $user !== null && $this->likes()->where('user_id', $user->id)->exists();
    }

    public function storeLinks()
    {
        return $this->hasMany(StoreLink::class)->orderBy('position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? asset('storage/'.$this->cover_path) : null;
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function metaDescription(): string
    {
        return $this->meta_description ?: Str::limit(strip_tags($this->synopsis), 155);
    }

    /**
     * Cheap one-shot snapshot of what a reader is allowed to open in this book.
     *
     * @return array{owner: bool, authenticated: bool, granted: bool, purchased: bool}
     */
    public function readerPass(?User $user): array
    {
        $isOwner = $user !== null && $user->id === $this->user_id;

        return [
            'owner' => $isOwner,
            'authenticated' => $user !== null,
            'granted' => $user !== null && $this->grants()->where('user_id', $user->id)->exists(),
            'purchased' => $user !== null && $this->orders()
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->exists(),
        ];
    }

    public function priceLabel(): string
    {
        $amount = (float) $this->price;

        return $this->currency.' '.number_format($amount, $amount === floor($amount) ? 0 : 2, ',', '.');
    }

    public function externalLinks()
    {
        return StoreLink::query()
            ->where('user_id', $this->user_id)
            ->where(fn ($query) => $query->whereNull('book_id')->orWhere('book_id', $this->id))
            ->orderBy('position')
            ->get();
    }
}
