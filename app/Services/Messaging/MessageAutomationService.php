<?php

namespace App\Services\Messaging;

use App\Models\MessageAutomation;
use App\Models\MessageThread;
use App\Models\User;
use App\Models\WorkOrder;

class MessageAutomationService
{
    public function triggers(): array
    {
        return [
            'work_order_status_changed' => 'Work order status changed',
            'appointment_upcoming_24h' => 'Appointment upcoming (24h)',
            'appointment_upcoming_2h' => 'Appointment upcoming (2h)',
            'technician_en_route' => 'Technician en route',
            'work_completed' => 'Work completed',
            'invoice_generated' => 'Invoice generated',
            'payment_received' => 'Payment received',
            'payment_overdue' => 'Payment overdue',
            'satisfaction_survey' => 'Satisfaction survey',
        ];
    }

    public function activeAutomations(): array
    {
        return MessageAutomation::query()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get()
            ->all();
    }

    public function buildContext(?WorkOrder $workOrder, ?User $recipient = null): array
    {
        return [
            'work_order_id' => $workOrder?->id ?? '',
            'work_order_subject' => $workOrder?->subject ?? '',
            'customer_name' => $workOrder?->organization?->name ?? '',
            'technician_name' => $workOrder?->assignedTo?->name ?? '',
            'appointment_time' => $workOrder?->scheduled_start_at?->format('M d, g:i A') ?? '',
            'recipient_name' => $recipient?->name ?? '',
        ];
    }

    public function resolveThread(WorkOrder $workOrder, ?User $sender = null): MessageThread
    {
        return MessageThread::firstOrCreate([
            'work_order_id' => $workOrder->id,
        ], [
            'subject' => 'Work Order #' . $workOrder->id,
            'organization_id' => $workOrder->organization_id,
            'created_by_user_id' => $sender?->id,
            'type' => 'work_order',
        ]);
    }
}
