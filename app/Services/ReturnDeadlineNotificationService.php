<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use SendGrid;

class ReturnDeadlineNotificationService
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * 返却期限メール送信
     *
     * @param string $message
     * @return void
     */
    public function send(string $message): void
    {
        $FROM = config('mail.notification.address');
        $TO = config('mail.notification.address');
        $SUBJECT = '[DeviceManager]返却期限通知';

        try {
            $from = new \SendGrid\Mail\From($FROM);
            $tos = [new \SendGrid\Mail\To($TO)];
            $subject = new \SendGrid\Mail\Subject($SUBJECT);
            $htmlContent = new \SendGrid\Mail\HtmlContent($message);
            $email = new \SendGrid\Mail\Mail(
                $from,
                $tos,
                $subject,
                null,
                $htmlContent
            );
            $sendgrid = new SendGrid(config('services.sendgrid.api_key'));
            $response = $sendgrid->send($email);
            if ($response->statusCode() != 202) {
                $this->logger->error('notification.email.failed', [
                    'service' => 'sendgrid',
                    'action' => 'return_deadline_notification',
                    'status_code' => $response->statusCode(),
                    'body' => $response->body(),
                    'from' => $FROM,
                    'to' => $TO,
                ]);
            } else {
                $this->logger->info('notification.email.sent', [
                    'service' => 'sendgrid',
                    'action' => 'return_deadline_notification',
                    'from' => $FROM,
                    'to' => $TO,
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('notification.email.exception', [
                'service' => 'sendgrid',
                'action' => 'return_deadline_notification',
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
        }
    }
}
