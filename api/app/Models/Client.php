<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    //primaryKeyの変更
    protected $primaryKey = 'client_id';
    protected $keyType = 'string';
    public $incrementing = false;
    //カラム名の変更対応
    public const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'client_id',
        'company',
        'url',
        'tel',
        'post_code',
        'street_address',
        'note',
        'created_at',
        'modified_at',
        'soft_deleted_at',
    ];

    public function contacts()
    {
        return $this->hasMany('App\Models\Contacts', 'client_id', 'client_id');
    }
}
