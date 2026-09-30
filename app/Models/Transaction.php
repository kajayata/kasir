<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'transaction_number', 'user_id', 'customer_id',
    'subtotal', 'discount', 'tax', 'other_fee', 'grand_total',
    'paid_amount', 'change_amount', 'payment_method', 'status',
])]
class Transaction extends Model
{
    use HasFactory;

    public const STATUS_PAID = 'paid';

    public const STATUS_HELD = 'held';

    public const STATUS_CANCELLED = 'cancelled';

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount' => 'integer',
            'tax' => 'integer',
            'other_fee' => 'integer',
            'grand_total' => 'integer',
            'paid_amount' => 'integer',
            'change_amount' => 'integer',
        ];
    }
}
