<?php

namespace App\Http\Controllers\Messaging;

use App\Services\Messaging\InboundMessageService;
use Illuminate\Http\Request;

class InboundEmailController
{
    public function __invoke(Request $request, InboundMessageService $service)
    {
        $message = $service->handleEmail($request->all());

        return response()->json([
            'received' => (bool) $message,
            'message_id' => $message?->id,
        ]);
    }
}
