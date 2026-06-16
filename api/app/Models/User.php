<?php

namespace App\Models;

use App\Notifications\CustomEmailChangeNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * パスワードリセット通知の送信(オーバーライド) : ログイン中
     * @access public
     * @param  string  $token
     */
    public function sendEmailChangeNotification(string $token)
    {

        $this->notify(new CustomEmailChangeNotification($token, $this->email));
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function rentalhists()
    {
        return $this->hasMany('App\Models\RentalHist', 'staff', 'id');
    }
}
