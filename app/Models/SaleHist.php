<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleHist extends Model
{
    use HasFactory;

    //primaryKeyの変更
    protected $primaryKey = 'sale_id';
    protected $keyType = 'string';
    public $incrementing = false;
    //カラム名の変更対応
    public const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'sale_id',
        'client',
        'contact',
        'staff',
        'sale_date_at',
        'note',
        'created_at',
        'modified_at',
        'soft_deleted_at',
    ];

    protected $casts = [
        'sale_date_at' => 'datetime',
    ];

    public function devices()
    {
        return $this->belongsToMany('App\Models\Device', 'device_sale', 'sale_id', 'device_id')
        ->withPivot('sale_date_at');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'staff', 'id');
    }

    public function contacts()
    {
        return $this->belongsTo('App\Models\Contacts', 'contact', 'id');
    }

    public function clients()
    {
        return $this->belongsTo('App\Models\Client', 'client', 'client_id');
    }
}
