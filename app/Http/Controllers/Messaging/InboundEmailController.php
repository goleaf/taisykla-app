<?php

namespace App\Http\Controllers\Messaging;

use App\Services\Messaging\InboundMessageService;
use Illuminate\Http\Request;

class InboundEmailController
{
    public function __invoke(Request $request, InboundMessageService $service)
    {
        $validated = $request->validate([
            'from' => 'required|string',
            'subject' => 'nullable|string',
            'text' => 'nullable|string',
            'body' => 'nullable|string',
        ]);

        $message = $service->handleEmail($validated);

        return response()->json([
            'received' => (bool) $message,
            'message_id' => $message?->id,
        ]);
    }
}
