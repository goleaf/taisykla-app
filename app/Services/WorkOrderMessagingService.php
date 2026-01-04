<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderMessagingService
{
    public function ensureThread(WorkOrder $workOrder, ?User $actor = null, ?User $fallbackParticipant = null): MessageThread
    {
        $thread = MessageThread::where('work_order_id', $workOrder->id)
            ->latest()
            ->first();

        if (! $thread) {
            $thread = MessageThread::create([
                'subject' => 'Work Order #' . $workOrder->id,
                'organization_id' => $workOrder->organization_id,
                'work_order_id' => $workOrder->id,
                'created_by_user_id' => $actor?->id,
                'type' => 'work_order',
            ]);
        }

        $participantIds = [
            $workOrder->requested_by_user_id,
            $workOrder->assigned_to_user_id,
            $actor?->id,
            $fallbackParticipant?->id,
        ];

        foreach (array_unique(array_filter($participantIds)) as $userId) {
            MessageThreadParticipant::firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => $userId,
            ], [
                'folder' => 'inbox',
            ]);
        }

        return $thread;
    }

    public function postMessage(WorkOrder $workOrder, User $actor, string $body, ?User $fallbackParticipant = null): Message
    {
        $thread = $this->ensureThread($workOrder, $actor, $fallbackParticipant);

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $actor->id,
            'sender_id' => $actor->id,
            'subject' => $thread->subject,
            'body' => $body,
            'timestamp' => now(),
            'message_type' => 'work_order',
            'channel' => 'in_app',
            'related_work_order_id' => $workOrder->id,
        ]);

        $thread->touch();

        return $message;
    }
}
