<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'barcode', 'name', 'price', 'stock', 'image'])]
class Product extends Model
{
    use HasFactory;

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    #[Scope]
    protected function cari(Builder $query, ?string $keyword): void
    {
        if (blank($keyword)) {
            return;
        }

        $like = '%'.strtolower($keyword).'%';

        $query->where(function ($q) use ($like) {
            $q->whereRaw('lower(name) like ?', [$like])
                ->orWhereRaw('lower(code) like ?', [$like])
                ->orWhere('barcode', 'like', $like);
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }
}
