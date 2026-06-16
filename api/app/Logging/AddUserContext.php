<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Monolog\LogRecord;

class AddUserContext
{
    public function __invoke(Logger $logger)
    {
        $logger->pushProcessor(function (LogRecord $record) {
            $user = Auth::user();
            $record->extra['user_id'] = $user?->id;
            $record->extra['login_user'] = $user?->name;

            return $record;
        });
    }
}
