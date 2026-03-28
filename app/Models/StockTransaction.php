<?php

namespace App\Models;

use App\Enums\TrackingType;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_type',
        'transaction_type',
        'item_id',
        'inventory_unit_id',
        'inventory_stock_id',
        'quantity_change',
        'reason',
        'note',
        'staff_id',
        'transacted_at',
    ];

    protected $casts = [
        'tracking_type'    => TrackingType::class,
        'transaction_type' => TransactionType::class,
        'quantity_change'  => 'integer',
        'transacted_at'    => 'datetime',
    ];

    /** 品目マスタ */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * 個別管理の場合の個体（device）
     * tracking_type='individual' のときのみ有効
     */
    public function inventoryUnit(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'inventory_unit_id', 'device_id');
    }

    /**
     * 数量管理の場合のロケーション別在庫
     * tracking_type='quantity' のときのみ有効
     */
    public function inventoryStock(): BelongsTo
    {
        return $this->belongsTo(InventoryStock::class, 'inventory_stock_id');
    }

    /** 処理担当者 */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
