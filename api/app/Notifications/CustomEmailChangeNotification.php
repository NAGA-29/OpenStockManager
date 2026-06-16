<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
// Mails
// use App\Mail\CustomResetPasswordMail;
// Libraries
use Illuminate\Notifications\Notification;

class CustomEmailChangeNotification extends Notification
{
    use Queueable;

    public $email;
    public $token;

    /**
     * コンストラクタ
     * @access public
     * @param  string  $token
     * @return void
     */
    public function __construct(string $token, string $email)
    {
        $this->email = $email;
        // email変更用のトークンを設定
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(route('profile.email.verify', [
            'token' => $this->token,
        ], false));

        // $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        // $expire = Carbon::now()->addMinutes($expireMinutes)->format('Y年m月d日 H時i分s秒'); // 有効期限

        return (new MailMessage())
            ->subject('OpenStockManager メールアドレス認証通知')
            ->view('emails.email_change', compact('url'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
