<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalHist extends Model
{
    use HasFactory;

    //primaryKeyの変更
    protected $primaryKey = 'lend_id';
    protected $keyType = 'string';
    public $incrementing = false;
    //カラム名の変更対応
    public const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'lend_id',
        'client',
        'contact',
        'staff',
        'all_returned',
        'checkout_at',
        'schedule_return_at',
        'return_at',
        'note',
        'created_at',
        'modified_at',
        'soft_deleted_at',
    ];

    protected $casts = [
        'checkout_at' => 'datetime',
        'schedule_return_at' => 'datetime',
        'return_at' => 'datetime',
    ];

    public function deadLineCheck(int $day): string
    {
        $today = Carbon::today();
        $check_day = $today->copy()->addDays($day);
        $deadlines = RentalHist::where('schedule_return_at', $check_day)->get();
        return $this->makeDeadLineMessage($deadlines);
    }

    private function makeDeadLineMessage(Collection $deadlines): string
    {
        if ($deadlines->isEmpty()) {
            return '';
        }

        $Message  = '下記の返却期限が迫っています。<br>';
        $Message .= '==================================<br>';
        if ($deadlines) {
            foreach ($deadlines as $dead) {
                $Message .= '<br>';
                $Message .= 'レンタルID: '. $dead->lend_id . '<br>';
                $Message .= 'クライアント名: '. $dead->clients->company . '様<br>';
                $Message .= '==================================<br>';
            }
        }
        return $Message;
    }

    public function devices()
    {
        return $this->belongsToMany('App\Models\Device', 'device_rental', 'lend_id', 'device_id')
        ->withPivot('checkout_at', 'return_at');
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
