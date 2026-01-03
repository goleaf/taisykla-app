<?php

namespace App\Services;

interface SmsGateway
{
    public function send(string $to, string $message): void;
}
