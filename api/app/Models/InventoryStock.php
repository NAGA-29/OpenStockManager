<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'location',
        'quantity',
        'min_stock',
    ];

    protected $casts = [
        'quantity'  => 'integer',
        'min_stock' => 'integer',
    ];

    /** 対応する品目マスタ */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /** この在庫レコードに紐づく入出庫トランザクション */
    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class, 'inventory_stock_id');
    }

    /** 最低在庫を下回っているか */
    public function isBelowMinStock(): bool
    {
        return $this->min_stock !== null && $this->quantity < $this->min_stock;
    }
}
