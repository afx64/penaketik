<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'book_id', 'platform', 'label', 'url', 'position'])]
class StoreLink extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function displayLabel(): string
    {
        return $this->label ?: $this->platform;
    }
}
