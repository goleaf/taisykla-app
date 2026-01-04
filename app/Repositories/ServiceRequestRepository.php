<?php

namespace App\Repositories;

use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ServiceRequestRepository
{
    /**
     * Get all service requests with filters and pagination.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ServiceRequest::query()
            ->with(['customer', 'equipment', 'technician', 'approvedBy']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['technician_id'])) {
            $query->where('technician_id', $filters['technician_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['date_to']));
        }

        // Default sorting
        $sortColumn = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // whitelist sort columns
        if (in_array($sortColumn, ['created_at', 'updated_at', 'scheduled_at', 'priority', 'status'])) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new service request.
     */
    public function create(array $data): ServiceRequest
    {
        return ServiceRequest::create($data);
    }

    /**
     * Find a service request by ID.
     */
    public function find(int $id): ?ServiceRequest
    {
        return ServiceRequest::with(['customer', 'equipment', 'technician', 'approvedBy', 'items', 'notes', 'attachments', 'activityLogs'])
            ->find($id);
    }

    /**
     * Find a service request by ID or fail.
     */
    public function findOrFail(int $id): ServiceRequest
    {
        return ServiceRequest::with(['customer', 'equipment', 'technician', 'approvedBy', 'items', 'notes', 'attachments', 'activityLogs'])
            ->findOrFail($id);
    }

    /**
     * Update a service request.
     */
    public function update(ServiceRequest $serviceRequest, array $data): bool
    {
        return $serviceRequest->update($data);
    }

    /**
     * Delete a service request.
     */
    public function delete(ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->delete();
    }
}
