<?php

namespace App\Observers;

use App\Models\ServiceRequest;
use App\Models\ActivityLog;

class ServiceRequestObserver
{
    /**
     * Handle the ServiceRequest "updated" event.
     */
    public function updated(ServiceRequest $serviceRequest): void
    {
        if ($serviceRequest->isDirty('status')) {
            $oldStatus = $serviceRequest->getOriginal('status');
            $newStatus = $serviceRequest->status;

            $serviceRequest->activityLogs()->create([
                'user_id' => auth()->id(),
                'action' => 'status_change',
                'description' => "Status changed from {$oldStatus} to {$newStatus}",
                'meta' => [
                    'from' => $oldStatus,
                    'to' => $newStatus,
                ],
            ]);
        }
    }

    /**
     * Handle the ServiceRequest "created" event.
     */
    public function created(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->activityLogs()->create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'description' => 'Service request created',
            'meta' => [
                'status' => $serviceRequest->status,
                'priority' => $serviceRequest->priority,
            ],
        ]);
    }
}