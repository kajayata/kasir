<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id', 'product_id',
    'product_code', 'product_name', 'price', 'qty', 'discount', 'subtotal',
])]
class TransactionDetail extends Model
{
    use HasFactory;

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'qty' => 'integer',
            'discount' => 'integer',
            'subtotal' => 'integer',
        ];
    }
}
