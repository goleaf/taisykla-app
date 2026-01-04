<?php

namespace App\Repositories;

use App\Models\ServiceRequest;
use App\Repositories\Contracts\ServiceRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ServiceRequestRepository
 *
 * Implementation of the ServiceRequestRepositoryInterface.
 * Provides data access layer for ServiceRequest model operations.
 */
class ServiceRequestRepository implements ServiceRequestRepositoryInterface
{
    /**
     * Cache TTL for statistics (5 minutes).
     */
    protected const STATISTICS_CACHE_TTL = 300;

    /**
     * Cache key prefix for statistics.
     */
    protected const STATISTICS_CACHE_KEY = 'service_request_statistics';

    /**
     * Default relations to eager load.
     */
    protected array $defaultRelations = [
        'customer',
        'equipment',
        'technician',
        'approvedBy',
    ];

    /**
     * Full relations for detailed view.
     */
    protected array $fullRelations = [
        'customer',
        'equipment',
        'technician',
        'approvedBy',
        'items',
        'notes',
        'attachments',
        'activityLogs',
    ];

    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected ServiceRequest $model
    ) {
        $this->enableQueryLogging();
    }

    /**
     * Enable query logging in debug mode.
     */
    protected function enableQueryLogging(): void
    {
        if (config('app.debug')) {
            DB::listen(function ($query) {
                if (str_contains($query->sql, 'service_requests')) {
                    Log::debug('ServiceRequestRepository Query', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms',
                    ]);
                }
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery()
            ->with($this->defaultRelations);

        $query = $this->applyFilters($query, $filters);
        $query = $this->applySorting($query, $filters);

        // Return paginated results if per_page is specified or default behavior
        $perPage = $filters['per_page'] ?? 15;

        if (isset($filters['paginate']) && $filters['paginate'] === false) {
            return $query->get();
        }

        return $query->paginate($perPage);
    }

    /**
     * Apply filters to query builder.
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
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

        if (!empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (!empty($filters['equipment_id'])) {
            $query->where('equipment_id', $filters['equipment_id']);
        }

        return $query;
    }

    /**
     * Apply sorting to query builder.
     */
    protected function applySorting(Builder $query, array $filters): Builder
    {
        $sortColumn = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // Whitelist allowed sort columns
        $allowedSortColumns = [
            'created_at',
            'updated_at',
            'scheduled_at',
            'priority',
            'status',
            'estimated_cost',
            'actual_cost',
        ];

        if (in_array($sortColumn, $allowedSortColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?ServiceRequest
    {
        try {
            return $this->model->newQuery()
                ->with($this->fullRelations)
                ->find($id);
        } catch (\Exception $e) {
            Log::error('ServiceRequestRepository::find error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Find or fail with exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): ServiceRequest
    {
        return $this->model->newQuery()
            ->with($this->fullRelations)
            ->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): ServiceRequest
    {
        try {
            DB::beginTransaction();

            $serviceRequest = $this->model->newInstance($data);
            $serviceRequest->save();

            // Clear statistics cache when new request is created
            $this->clearStatisticsCache();

            DB::commit();

            return $serviceRequest->load($this->defaultRelations);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceRequestRepository::create error', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): ServiceRequest
    {
        try {
            DB::beginTransaction();

            $serviceRequest = $this->findOrFail($id);
            $serviceRequest->update($data);

            // Clear statistics cache when request is updated
            $this->clearStatisticsCache();

            DB::commit();

            return $serviceRequest->fresh($this->defaultRelations);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceRequestRepository::update error', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        try {
            $serviceRequest = $this->findOrFail($id);
            $result = $serviceRequest->delete();

            // Clear statistics cache
            $this->clearStatisticsCache();

            return $result;
        } catch (\Exception $e) {
            Log::error('ServiceRequestRepository::delete error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restore(int $id): bool
    {
        try {
            $serviceRequest = $this->model->newQuery()
                ->withTrashed()
                ->findOrFail($id);

            $result = $serviceRequest->restore();

            // Clear statistics cache
            $this->clearStatisticsCache();

            return $result;
        } catch (\Exception $e) {
            Log::error('ServiceRequestRepository::restore error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forceDelete(int $id): bool
    {
        try {
            $serviceRequest = $this->model->newQuery()
                ->withTrashed()
                ->findOrFail($id);

            $result = $serviceRequest->forceDelete();

            // Clear statistics cache
            $this->clearStatisticsCache();

            return $result;
        } catch (\Exception $e) {
            Log::error('ServiceRequestRepository::forceDelete error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWithRelations(int $id, array $relations): ?ServiceRequest
    {
        try {
            return $this->model->newQuery()
                ->with($relations)
                ->find($id);
        } catch (\Exception $e) {
            Log::error('ServiceRequestRepository::getWithRelations error', [
                'id' => $id,
                'relations' => $relations,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingForTechnician(int $technicianId): Collection
    {
        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->where('technician_id', $technicianId)
            ->pending()
            ->orderBy('scheduled_at', 'asc')
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getOverdueRequests(): Collection
    {
        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', Carbon::now())
            ->whereNotIn('status', [
                ServiceRequest::STATUS_COMPLETED,
                ServiceRequest::STATUS_CANCELLED,
            ])
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getByCustomer(int $customerId, bool $paginate = true): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery()
            ->with($this->defaultRelations)
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc');

        if ($paginate) {
            return $query->paginate(15);
        }

        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $cacheKey = $this->buildStatisticsCacheKey($dateFrom, $dateTo);

        return Cache::remember($cacheKey, self::STATISTICS_CACHE_TTL, function () use ($dateFrom, $dateTo) {
            $query = $this->model->newQuery();

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', Carbon::parse($dateFrom));
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', Carbon::parse($dateTo));
            }

            // Count by status
            $statusCounts = (clone $query)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Count by priority
            $priorityCounts = (clone $query)
                ->select('priority', DB::raw('COUNT(*) as count'))
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray();

            // Total count
            $totalCount = (clone $query)->count();

            // Additional useful statistics
            $overdueCount = $this->getOverdueRequests()->count();

            $avgCompletionTime = $this->model->newQuery()
                ->whereNotNull('completed_at')
                ->whereNotNull('created_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
                ->value('avg_hours');

            return [
                'total' => $totalCount,
                'by_status' => $statusCounts,
                'by_priority' => $priorityCounts,
                'overdue' => $overdueCount,
                'avg_completion_hours' => round($avgCompletionTime ?? 0, 2),
            ];
        });
    }

    /**
     * Build cache key for statistics.
     */
    protected function buildStatisticsCacheKey(?string $dateFrom, ?string $dateTo): string
    {
        $key = self::STATISTICS_CACHE_KEY;

        if ($dateFrom) {
            $key .= '_from_' . $dateFrom;
        }

        if ($dateTo) {
            $key .= '_to_' . $dateTo;
        }

        return $key;
    }

    /**
     * Clear statistics cache.
     */
    protected function clearStatisticsCache(): void
    {
        // Clear the base statistics cache key
        Cache::forget(self::STATISTICS_CACHE_KEY);

        // Note: For date-ranged cache keys, they will expire naturally.
        // For a more aggressive approach, you could implement a cache tagging strategy.
    }

    /**
     * {@inheritdoc}
     */
    public function searchByKeyword(string $keyword): Collection
    {
        $searchTerm = '%' . $keyword . '%';

        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->where(function (Builder $query) use ($searchTerm) {
                $query->where('description', 'LIKE', $searchTerm)
                    ->orWhere('customer_notes', 'LIKE', $searchTerm)
                    ->orWhere('technician_notes', 'LIKE', $searchTerm)
                    ->orWhere('internal_notes', 'LIKE', $searchTerm);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpcoming(int $days = 7): Collection
    {
        $now = Carbon::now();
        $endDate = Carbon::now()->addDays($days);

        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$now, $endDate])
            ->whereNotIn('status', [
                ServiceRequest::STATUS_COMPLETED,
                ServiceRequest::STATUS_CANCELLED,
            ])
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletedInDateRange(string $from, string $to): Collection
    {
        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->where('status', ServiceRequest::STATUS_COMPLETED)
            ->whereBetween('completed_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ])
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus(int $id, string $status, int $userId): ServiceRequest
    {
        try {
            DB::beginTransaction();

            $serviceRequest = $this->findOrFail($id);
            $oldStatus = $serviceRequest->status;

            $updateData = ['status' => $status];

            // Set timestamps based on status change
            if ($status === ServiceRequest::STATUS_IN_PROGRESS && !$serviceRequest->started_at) {
                $updateData['started_at'] = Carbon::now();
            }

            if ($status === ServiceRequest::STATUS_COMPLETED && !$serviceRequest->completed_at) {
                $updateData['completed_at'] = Carbon::now();
            }

            $serviceRequest->update($updateData);

            // Log the status change
            Log::info('ServiceRequest status updated', [
                'service_request_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'updated_by' => $userId,
            ]);

            // Clear statistics cache
            $this->clearStatisticsCache();

            DB::commit();

            return $serviceRequest->fresh($this->defaultRelations);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceRequestRepository::updateStatus error', [
                'id' => $id,
                'status' => $status,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assignTechnician(int $id, int $technicianId): ServiceRequest
    {
        try {
            DB::beginTransaction();

            $serviceRequest = $this->findOrFail($id);
            $oldTechnicianId = $serviceRequest->technician_id;

            $serviceRequest->update([
                'technician_id' => $technicianId,
                'status' => ServiceRequest::STATUS_ASSIGNED,
            ]);

            // Log the assignment
            Log::info('ServiceRequest technician assigned', [
                'service_request_id' => $id,
                'old_technician_id' => $oldTechnicianId,
                'new_technician_id' => $technicianId,
            ]);

            // Clear statistics cache
            $this->clearStatisticsCache();

            DB::commit();

            return $serviceRequest->fresh($this->defaultRelations);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ServiceRequestRepository::assignTechnician error', [
                'id' => $id,
                'technician_id' => $technicianId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get service requests for a specific equipment.
     *
     * @param int $equipmentId
     * @param bool $paginate
     * @return LengthAwarePaginator|Collection
     */
    public function getByEquipment(int $equipmentId, bool $paginate = true): LengthAwarePaginator|Collection
    {
        $query = $this->model->newQuery()
            ->with($this->defaultRelations)
            ->where('equipment_id', $equipmentId)
            ->orderBy('created_at', 'desc');

        if ($paginate) {
            return $query->paginate(15);
        }

        return $query->get();
    }

    /**
     * Get active (non-completed, non-cancelled) requests.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }

    /**
     * Get high priority requests.
     *
     * @return Collection
     */
    public function getHighPriority(): Collection
    {
        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->highPriority()
            ->active()
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }

    /**
     * Check if a service request exists.
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->model->newQuery()->where('id', $id)->exists();
    }

    /**
     * Count service requests by status.
     *
     * @param string $status
     * @return int
     */
    public function countByStatus(string $status): int
    {
        return $this->model->newQuery()
            ->where('status', $status)
            ->count();
    }

    /**
     * Get recently created service requests.
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->with($this->defaultRelations)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
