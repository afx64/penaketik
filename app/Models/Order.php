<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'book_id', 'reference', 'status', 'amount', 'currency', 'proof_path', 'note', 'approved_at'])]
class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'rejected', 'cancelled'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function amountLabel(): string
    {
        $amount = (float) $this->amount;

        return $this->currency.' '.number_format($amount, $amount === floor($amount) ? 0 : 2, ',', '.');
    }
}
