<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ServiceRequest $serviceRequest, public string $oldStatus, public string $newStatus)
    {
    }
}
