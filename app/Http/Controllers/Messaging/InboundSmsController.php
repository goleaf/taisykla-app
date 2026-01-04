<?php

namespace App\Http\Controllers\Messaging;

use App\Services\Messaging\InboundMessageService;
use Illuminate\Http\Request;

class InboundSmsController
{
    public function __invoke(Request $request, InboundMessageService $service)
    {
        $validated = $request->validate([
            'from' => 'required_without:From|string',
            'From' => 'required_without:from|string',
            'body' => 'nullable|string',
            'Body' => 'nullable|string',
        ]);

        $payload = [
            'from' => $validated['from'] ?? $validated['From'] ?? null,
            'body' => $validated['body'] ?? $validated['Body'] ?? '',
        ];

        $message = $service->handleSms($payload);

        return response()->json([
            'received' => (bool) $message,
            'message_id' => $message?->id,
        ]);
    }
}
