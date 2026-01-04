<?php

namespace App\Repositories\Contracts;

use App\Models\ServiceRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface ServiceRequestRepositoryInterface
 *
 * Defines the contract for Service Request repository operations.
 */
interface ServiceRequestRepositoryInterface
{
    /**
     * Get all service requests with optional filters.
     *
     * @param array $filters Available filters: status, priority, customer_id, technician_id, date_from, date_to
     * @return LengthAwarePaginator|Collection
     */
    public function all(array $filters = []): LengthAwarePaginator|Collection;

    /**
     * Find a service request by ID with relationships loaded.
     *
     * @param int $id
     * @return ServiceRequest|null
     */
    public function find(int $id): ?ServiceRequest;

    /**
     * Create a new service request.
     *
     * @param array $data
     * @return ServiceRequest
     */
    public function create(array $data): ServiceRequest;

    /**
     * Update an existing service request.
     *
     * @param int $id
     * @param array $data
     * @return ServiceRequest
     */
    public function update(int $id, array $data): ServiceRequest;

    /**
     * Soft delete a service request.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restore a soft-deleted service request.
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool;

    /**
     * Permanently delete a service request.
     *
     * @param int $id
     * @return bool
     */
    public function forceDelete(int $id): bool;

    /**
     * Get a service request with specific relations.
     *
     * @param int $id
     * @param array $relations
     * @return ServiceRequest|null
     */
    public function getWithRelations(int $id, array $relations): ?ServiceRequest;

    /**
     * Get pending requests for a specific technician.
     *
     * @param int $technicianId
     * @return Collection
     */
    public function getPendingForTechnician(int $technicianId): Collection;

    /**
     * Get requests that are past their scheduled_at and not completed.
     *
     * @return Collection
     */
    public function getOverdueRequests(): Collection;

    /**
     * Get all requests for a specific customer.
     *
     * @param int $customerId
     * @param bool $paginate
     * @return LengthAwarePaginator|Collection
     */
    public function getByCustomer(int $customerId, bool $paginate = true): LengthAwarePaginator|Collection;

    /**
     * Get statistics (counts by status and priority).
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    public function getStatistics(?string $dateFrom = null, ?string $dateTo = null): array;

    /**
     * Search service requests by keyword in description and notes.
     *
     * @param string $keyword
     * @return Collection
     */
    public function searchByKeyword(string $keyword): Collection;

    /**
     * Get requests scheduled in the next X days.
     *
     * @param int $days
     * @return Collection
     */
    public function getUpcoming(int $days = 7): Collection;

    /**
     * Get completed requests within a date range.
     *
     * @param string $from
     * @param string $to
     * @return Collection
     */
    public function getCompletedInDateRange(string $from, string $to): Collection;

    /**
     * Update status and log the change.
     *
     * @param int $id
     * @param string $status
     * @param int $userId
     * @return ServiceRequest
     */
    public function updateStatus(int $id, string $status, int $userId): ServiceRequest;

    /**
     * Assign a technician to a service request.
     *
     * @param int $id
     * @param int $technicianId
     * @return ServiceRequest
     */
    public function assignTechnician(int $id, int $technicianId): ServiceRequest;
}
