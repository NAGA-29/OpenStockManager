<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeviceTypeField extends Model
{
    protected $fillable = [
        'device_category_code',
        'field_key',
        'label',
        'field_type',
        'options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
        'sort_order'  => 'integer',
    ];

    public const FIELD_TYPES = [
        'text'    => 'テキスト',
        'number'  => '数値',
        'select'  => 'セレクト',
        'boolean' => 'チェックボックス',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DeviceCategory::class, 'device_category_code', 'code');
    }

    /**
     * Generate a unique field_key from label within the same category.
     */
    public static function generateFieldKey(string $label, string $categoryCode): string
    {
        $base = Str::snake(preg_replace('/[^\w\s]/u', '', $label));
        $key  = $base;
        $i    = 1;

        while (static::where('device_category_code', $categoryCode)->where('field_key', $key)->exists()) {
            $key = $base . '_' . $i++;
        }

        return $key;
    }
}
