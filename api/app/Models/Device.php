<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Device extends Model
{
    use HasFactory;

    //primaryKeyの変更
    protected $primaryKey = 'device_id';
    protected $keyType = 'string';
    public $incrementing = false;
    //カラム名の変更対応
    public const UPDATED_AT = 'modified_at';

    // キャストする
    protected $casts = [
        'custom_fields'         => 'array',
        'first_work_date_at'    => 'date:Y-m-d',
        'purchase_date_at'      => 'date:Y-m-d',
        'sale_date_at'          => 'date:Y-m-d',
        'soft_deleted_at'       => 'date:Y-m-d',
    ];

    protected $fillable = [
        'device_id',
        'device_type',
        'device_name',
        'device_serial',
        'custom_fields',
        'first_work_date_at',
        'purchase_date_at',
        'client',
        'sale_date_at',
        'option',
        'condition_id',
        'defective',
        'not_for_sale',
        'note',
        'lending_now',
        'using_user_id',
        'created_at',
        'modified_at',
        'soft_deleted_at',
    ];

    public function condition()
    {
        return $this->belongsTo('App\Models\Condition', 'condition_id', 'id');
    }

    /**
     * デバイスカテゴリとのリレーション
     */
    public function category()
    {
        return $this->belongsTo(DeviceCategory::class, 'device_type', 'code');
    }

    public function contents()
    {
        return $this->hasMany('App\Models\Content', 'device_id', 'device_id');
    }
    /**
     * retnal_histsテーブルとのリレーション
     */
    public function rental_hists()
    {
        return $this->belongsToMany('App\Models\RentalHist', 'device_rental', 'device_id', 'lend_id')
        ->withPivot('checkout_at', 'return_at');
    }

    /**
     * sale_histsテーブルとのリレーション
     */
    public function sale_hists()
    {
        return $this->belongsToMany('App\Models\SaleHist', 'device_sale', 'device_id', 'sale_id')
        ->withPivot('sale_date_at');
    }

    /**
     * デバイス種別ごとのデータを取得
     *
     * @access public
     * @param string $deviceType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByType(string $deviceType)
    {
        return static::where('device_type', $deviceType)->get();
    }

    public static function getStbInfo()
    {
        return static::getByType('STB');
    }

    public static function getCameraInfo()
    {
        return static::getByType('CAM');
    }

    public static function getTabletInfo()
    {
        return static::getByType('TAB');
    }

    public static function getSignageInfo()
    {
        return static::getByType('SIGN');
    }

    public static function getOtherDeviceInfo()
    {
        return static::getByType('OTH');
    }

    /**
     * devicesテーブルからdevice_idで値を取得する
     *
     * @access public
     * @param string $device_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getIndividualDeviceInfo($device_id)
    {
        return static::where('device_id', $device_id)->get();
    }

    /**
     * device_rentalテーブルからdevice_idで値を取得
     *
     * @access public
     * @param string $device_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getDeviceRentalHis($device_id)
    {
        return DB::table('device_rental')
                ->where('device_id', $device_id)
                ->get();
    }

    public function getDeviceSerialNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * device_idを自動生成する
     * フォーマット: <device_type>_<device_name>_000001
     *
     * @param string $deviceType デバイス区分コード
     * @param string $deviceName デバイス名
     * @return string 生成されたdevice_id
     */
    public static function generateDeviceId(string $deviceType, string $deviceName): string
    {
        $prefix = "{$deviceType}_{$deviceName}_";

        $lastDevice = static::where('device_id', 'like', $prefix . '%')
            ->orderBy('device_id', 'desc')
            ->first();

        if ($lastDevice) {
            $lastNum = (int) substr($lastDevice->device_id, strlen($prefix));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return $prefix . str_pad($newNum, 6, '0', STR_PAD_LEFT);
    }

}
