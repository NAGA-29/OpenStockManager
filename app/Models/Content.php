<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'filename',
        'extension',
        'hash',
        'path',
        'height',
        'width',
        'size',
        'thumbnail',
        'device_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'modified_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }
}
