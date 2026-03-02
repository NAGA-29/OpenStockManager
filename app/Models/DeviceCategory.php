<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * アクティブなカテゴリをソート順で取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * ソート順で並べる
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * このカテゴリに属するデバイス
     */
    public function devices()
    {
        return $this->hasMany(Device::class, 'device_type', 'code');
    }

    /**
     * このカテゴリのカスタムフィールド定義
     */
    public function fields()
    {
        return $this->hasMany(DeviceTypeField::class, 'device_category_code', 'code')
                    ->orderBy('sort_order')
                    ->orderBy('id');
    }

    /**
     * アクティブなカテゴリコード一覧を返す（バリデーション用）
     */
    public static function activeCodes(): array
    {
        return static::active()->ordered()->pluck('code')->toArray();
    }
}
