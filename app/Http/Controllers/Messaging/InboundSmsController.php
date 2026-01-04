<?php

namespace App\Http\Controllers\Messaging;

use App\Services\Messaging\InboundMessageService;
use Illuminate\Http\Request;

class InboundSmsController
{
    public function __invoke(Request $request, InboundMessageService $service)
    {
        $message = $service->handleSms($request->all());

        return response()->json([
            'received' => (bool) $message,
            'message_id' => $message?->id,
        ]);
    }
}
