<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): void
    {
        Log::info('SMS dispatched', ['to' => $to, 'message' => $message]);
    }
}
