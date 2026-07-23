<?php

namespace App\Models;

use App\Enums\TrackingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_code',
        'tracking_type',
        'unit',
        'custom_fields',
        'is_active',
    ];

    protected $casts = [
        'tracking_type' => TrackingType::class,
        'custom_fields' => 'array',
        'is_active'     => 'boolean',
    ];

    /** 品目が属するカテゴリ */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DeviceCategory::class, 'category_code', 'code');
    }

    /** 個別管理: この品目に紐づく個体 (devices) */
    public function inventoryUnits(): HasMany
    {
        return $this->hasMany(Device::class, 'item_id');
    }

    /** 数量管理: この品目のロケーション別在庫 */
    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'item_id');
    }

    /** 入出庫トランザクション */
    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class, 'item_id');
    }

    public function isIndividual(): bool
    {
        return $this->tracking_type === TrackingType::Individual;
    }

    public function isQuantity(): bool
    {
        return $this->tracking_type === TrackingType::Quantity;
    }
}
