<?php

namespace App\Services;

class NullSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): void
    {
        // Intentionally left blank for disabled SMS.
    }
}
