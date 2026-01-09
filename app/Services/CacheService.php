<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Organization;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class CacheService
 *
 * Centralized cache management with tagging and invalidation strategies.
 * Provides consistent caching patterns across the application.
 */
class CacheService
{
    /**
     * Cache TTL constants in seconds.
     */
    public const TTL_SHORT = 300;       // 5 minutes - Dashboard statistics
    public const TTL_MEDIUM = 3600;     // 1 hour - Customer data, service requests
    public const TTL_LONG = 86400;      // 24 hours - Reports
    public const TTL_FOREVER = null;    // Forever - Reference data

    /**
     * Cache tag constants.
     */
    public const TAG_SERVICE_REQUESTS = 'service-requests';
    public const TAG_CUSTOMERS = 'customers';
    public const TAG_EQUIPMENT = 'equipment';
    public const TAG_REPORTS = 'reports';
    public const TAG_DASHBOARD = 'dashboard';
    public const TAG_USERS = 'users';

    /**
     * Check if the cache driver supports tags.
     */
    protected function supportsTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'array']);
    }

    /**
     * Get the cache instance, with tags if supported.
     *
     * @param array|string $tags
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function cache(array|string $tags = []): \Illuminate\Contracts\Cache\Repository
    {
        if ($this->supportsTags() && !empty($tags)) {
            return Cache::tags((array) $tags);
        }

        return Cache::store();
    }

    /**
     * Remember a service request with its relations.
     *
     * @param int $id
     * @param int $ttl
     * @return ServiceRequest|null
     */
    public function rememberServiceRequest(int $id, int $ttl = self::TTL_MEDIUM): ?ServiceRequest
    {
        $key = $this->buildKey('service-request', $id);

        return $this->cache(self::TAG_SERVICE_REQUESTS)
            ->remember($key, $ttl, function () use ($id) {
                return ServiceRequest::with(['customer', 'equipment', 'technician'])->find($id);
            });
    }

    /**
     * Forget a cached service request.
     *
     * @param int $id
     * @return bool
     */
    public function forgetServiceRequest(int $id): bool
    {
        $key = $this->buildKey('service-request', $id);
        return $this->cache(self::TAG_SERVICE_REQUESTS)->forget($key);
    }

    /**
     * Flush all service request caches.
     *
     * @return void
     */
    public function flushServiceRequests(): void
    {
        if ($this->supportsTags()) {
            Cache::tags(self::TAG_SERVICE_REQUESTS)->flush();
        } else {
            // For non-tag drivers, clear specific known keys
            Cache::forget('service_request_statistics');
        }

        Log::debug('CacheService: Flushed service requests cache');
    }

    /**
     * Remember customer data with equipment and service requests.
     *
     * @param int $customerId
     * @param int $ttl
     * @return Organization|null
     */
    public function rememberCustomer(int $customerId, int $ttl = self::TTL_MEDIUM): ?Organization
    {
        $key = $this->buildKey('customer', $customerId);

        return $this->cache([self::TAG_CUSTOMERS, self::TAG_SERVICE_REQUESTS])
            ->remember($key, $ttl, function () use ($customerId) {
                return Organization::with(['equipment', 'users'])->find($customerId);
            });
    }

    /**
     * Forget a cached customer.
     *
     * @param int $customerId
     * @return bool
     */
    public function forgetCustomer(int $customerId): bool
    {
        $key = $this->buildKey('customer', $customerId);
        return $this->cache(self::TAG_CUSTOMERS)->forget($key);
    }

    /**
     * Flush all customer caches.
     *
     * @return void
     */
    public function flushCustomers(): void
    {
        if ($this->supportsTags()) {
            Cache::tags(self::TAG_CUSTOMERS)->flush();
        }

        Log::debug('CacheService: Flushed customers cache');
    }

    /**
     * Remember equipment types (reference data - cached forever).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rememberEquipmentTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::rememberForever('equipment.types', function () {
            return EquipmentType::all();
        });
    }

    /**
     * Forget equipment types cache.
     *
     * @return bool
     */
    public function forgetEquipmentTypes(): bool
    {
        return Cache::forget('equipment.types');
    }

    /**
     * Remember user permissions.
     *
     * @param int $userId
     * @param int $ttl
     * @return \Illuminate\Support\Collection
     */
    public function rememberUserPermissions(int $userId, int $ttl = self::TTL_MEDIUM): \Illuminate\Support\Collection
    {
        $key = $this->buildKey('user.permissions', $userId);

        return $this->cache(self::TAG_USERS)
            ->remember($key, $ttl, function () use ($userId) {
                $user = \App\Models\User::find($userId);
                if (!$user) {
                    return collect();
                }
                return $user->getAllPermissions()->pluck('name');
            });
    }

    /**
     * Forget user permissions cache.
     *
     * @param int $userId
     * @return bool
     */
    public function forgetUserPermissions(int $userId): bool
    {
        $key = $this->buildKey('user.permissions', $userId);
        return $this->cache(self::TAG_USERS)->forget($key);
    }

    /**
     * Remember dashboard statistics.
     *
     * @param int $ttl
     * @return array
     */
    public function rememberDashboardStatistics(int $ttl = self::TTL_SHORT): array
    {
        return $this->cache(self::TAG_DASHBOARD)
            ->remember('dashboard.statistics', $ttl, function () {
                return [
                    'total_active' => ServiceRequest::active()->count(),
                    'pending' => ServiceRequest::where('status', 'pending')->count(),
                    'in_progress' => ServiceRequest::where('status', 'in_progress')->count(),
                    'completed_today' => ServiceRequest::where('status', 'completed')
                        ->whereDate('completed_at', today())
                        ->count(),
                ];
            });
    }

    /**
     * Flush dashboard cache.
     *
     * @return void
     */
    public function flushDashboard(): void
    {
        if ($this->supportsTags()) {
            Cache::tags(self::TAG_DASHBOARD)->flush();
        } else {
            Cache::forget('dashboard.statistics');
        }

        Log::debug('CacheService: Flushed dashboard cache');
    }

    /**
     * Remember monthly report data.
     *
     * @param string $month Format: Y-m
     * @param int $ttl
     * @return array
     */
    public function rememberMonthlyReport(string $month, int $ttl = self::TTL_LONG): array
    {
        $key = $this->buildKey('reports.monthly', $month);

        return $this->cache(self::TAG_REPORTS)
            ->remember($key, $ttl, function () use ($month) {
                $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
                $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth();

                return [
                    'month' => $month,
                    'total_requests' => ServiceRequest::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                    'completed_requests' => ServiceRequest::where('status', 'completed')
                        ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
                        ->count(),
                    'generated_at' => now()->toIso8601String(),
                ];
            });
    }

    /**
     * Flush reports cache.
     *
     * @return void
     */
    public function flushReports(): void
    {
        if ($this->supportsTags()) {
            Cache::tags(self::TAG_REPORTS)->flush();
        }

        Log::debug('CacheService: Flushed reports cache');
    }

    /**
     * Warm critical caches.
     *
     * @return array Summary of warmed caches
     */
    public function warmCaches(): array
    {
        $warmed = [];

        // Equipment types (forever)
        $this->rememberEquipmentTypes();
        $warmed['equipment_types'] = true;

        // Dashboard statistics
        $this->rememberDashboardStatistics();
        $warmed['dashboard_statistics'] = true;

        Log::info('CacheService: Cache warming completed', $warmed);

        return $warmed;
    }

    /**
     * Build a cache key with prefix.
     *
     * @param string $prefix
     * @param mixed $identifier
     * @return string
     */
    protected function buildKey(string $prefix, mixed $identifier): string
    {
        return sprintf('%s.%s', $prefix, $identifier);
    }

    /**
     * Get cache statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'driver' => config('cache.default'),
            'supports_tags' => $this->supportsTags(),
            'prefix' => config('cache.prefix'),
        ];
    }
}
