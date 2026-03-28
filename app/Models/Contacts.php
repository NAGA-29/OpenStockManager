<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    use HasFactory;

    //カラム名の変更対応
    public const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'client_id',
        'name',
        'tel',
        'email',
        'note',
        'created_at',
        'modified_at',
        'soft_deleted_at',
    ];

    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_id', 'client_id');
    }
}
