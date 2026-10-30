<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['book_id', 'title', 'slug', 'position', 'access', 'content', 'is_published'])]
class Chapter extends Model
{
    public const ACCESS_LEVELS = [
        'public' => 'Public preview',
        'free' => 'Free (signed-in readers)',
        'limited' => 'Limited free (invited readers)',
        'paid' => 'Paid',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function isWithinPreview(): bool
    {
        return $this->position <= Book::PUBLIC_PREVIEW_LIMIT;
    }

    public function accessLabel(): string
    {
        return self::ACCESS_LEVELS[$this->access] ?? ucfirst($this->access);
    }

    /**
     * @return array{owner: bool, authenticated: bool, granted: bool, purchased: bool}
     */
    public function readableWith(array $pass): bool
    {
        if ($pass['owner']) {
            return true;
        }

        return match ($this->access) {
            // Only the opening chapters are ever served without an account.
            'public' => $this->isWithinPreview() && $this->is_published,
            'free' => $pass['authenticated'] && $this->is_published,
            'limited' => $pass['granted'] && $this->is_published,
            'paid' => $pass['purchased'] && $this->is_published,
            default => false,
        };
    }

    /**
     * What a locked reader has to do to open this chapter.
     */
    public function lockReason(array $pass): ?string
    {
        if ($this->readableWith($pass)) {
            return null;
        }

        return match ($this->access) {
            'limited' => 'grant',
            'paid' => 'purchase',
            default => 'login',
        };
    }

    public function excerpt(int $limit = 180): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->content))), $limit);
    }
}
