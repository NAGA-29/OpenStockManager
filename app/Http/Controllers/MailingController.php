<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMailRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use SendGrid;

class MailingController extends Controller
{
    public function index()
    {
        return view('mailform', ['users' => User::all()]);
    }

    public function sendMail(SendMailRequest $request)
    {
        $validated = $request->validated();
        $from = new \SendGrid\Mail\From(config('mail.from.address'));
        $tos = [];
        array_push($tos, new \SendGrid\Mail\To($validated['to_email']));
        $subject = new \SendGrid\Mail\Subject($validated['subject']);
        $htmlContent = new \SendGrid\Mail\HtmlContent($validated['body']);
        $email = new \SendGrid\Mail\Mail(
            $from,
            $tos,
            $subject,
            null,
            $htmlContent
        );
        $sendgrid = new SendGrid(config('services.sendgrid.api_key'));
        $response = $sendgrid->send($email);
        if ($response->statusCode() == 202) {
            return back()->with(['success' => 'E-mails successfully sent out!!']);
        }
        return back()->withErrors(json_decode($response->body())->errors);
    }

    public function returnDeadlineMail(string $Message)
    {
        $FROM = config('mail.notification.address');
        $TO = config('mail.notification.address');

        $SUBJECT = __('messages.return_deadline_subject');
        try {
            $from = new \SendGrid\Mail\From($FROM);
            $tos = [];
            array_push($tos, new \SendGrid\Mail\To($TO));
            $subject = new \SendGrid\Mail\Subject($SUBJECT);
            $htmlContent = new \SendGrid\Mail\HtmlContent($Message);
            $email = new \SendGrid\Mail\Mail(
                $from,
                $tos,
                $subject,
                null,
                $htmlContent
            );
            /* Create instance of Sendgrid SDK */
            $sendgrid = new SendGrid(config('services.sendgrid.api_key'));
            /* Send mail using sendgrid instance */
            $response = $sendgrid->send($email);
            if ($response->statusCode() == 202) {
                Log::channel('operation')->info(__('messages.return_deadline_mail_success'), []);
            } else {
                Log::channel('error')->error(__('messages.return_deadline_mail_failed'), [
                    'status_code' => $response->statusCode(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('error')->error(__('messages.return_deadline_mail_exception'), [
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
