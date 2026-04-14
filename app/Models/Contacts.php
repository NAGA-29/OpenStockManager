<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 連絡先担当者を管理するモデル。
 *
 * @property int|string $id
 * @property int|string $client_id
 * @property string $name
 * @property string|null $tel
 * @property string|null $email
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $modified_at
 * @property \Illuminate\Support\Carbon|null $soft_deleted_at
 * @property-read Client|null $client
 *
 * @method static \Illuminate\Database\Eloquent\Builder|self query()
 */
class Contacts extends Model
{
    use HasFactory;

    //カラム名の変更対応
    public const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id',
        'client_id',
        'name',
        'tel',
        'email',
        'note',
        'created_at',
        'modified_at',
        'soft_deleted_at',
    ];

    /**
     * 紐づくクライアント情報を取得する。
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo('App\Models\Client', 'client_id', 'client_id');
    }
}
