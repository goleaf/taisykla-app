<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwilioSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): void
    {
        $sid = config('sms.twilio.sid');
        $token = config('sms.twilio.token');
        $from = config('sms.twilio.from');

        if (! $sid || ! $token || ! $from) {
            throw new RuntimeException('Twilio SMS credentials are not configured.');
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Twilio SMS request failed.');
        }
    }
}
