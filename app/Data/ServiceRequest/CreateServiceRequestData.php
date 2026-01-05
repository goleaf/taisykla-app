<?php

namespace App\Data\ServiceRequest;

use App\Models\Equipment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Data object for creating new Service Requests.
 * 
 * Replaces StoreServiceRequestRequest with built-in validation.
 */
class CreateServiceRequestData extends Data
{
    public function __construct(
        #[Required, Exists('organizations', 'id')]
        public int $customer_id,

        #[Exists('equipment', 'id')]
        public ?int $equipment_id = null,

        #[Exists('users', 'id')]
        public ?int $technician_id = null,

        #[Required, In([ServiceRequest::PRIORITY_LOW, ServiceRequest::PRIORITY_MEDIUM, ServiceRequest::PRIORITY_HIGH, ServiceRequest::PRIORITY_URGENT])]
        public string $priority = ServiceRequest::PRIORITY_MEDIUM,

        #[Required, Min(10), Max(5000)]
        public string $description = '',

        #[After('now')]
        public ?string $scheduled_at = null,

        #[Numeric, Min(0.5), Max(100)]
        public ?float $estimated_hours = null,

        #[Numeric, Min(0)]
        public ?float $estimated_cost = null,

        #[Max(2000)]
        public ?string $customer_notes = null,

        #[Max(2000)]
        public ?string $internal_notes = null,
    ) {
    }

    /**
     * Additional validation rules beyond attributes
     */
    public static function rules(...$args): array
    {
        return [
            'equipment_id' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    if (empty($value)) {
                        return;
                    }

                    $data = request()->all();
                    $customerId = $data['customer_id'] ?? null;

                    if (empty($customerId)) {
                        return;
                    }

                    $equipment = Equipment::find($value);
                    if ($equipment && $equipment->customer_id !== (int) $customerId) {
                        $fail('The selected equipment does not belong to the selected customer.');
                    }
                },
            ],
            'technician_id' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    if (empty($value)) {
                        return;
                    }

                    $user = User::find($value);
                    if (!$user) {
                        $fail('The selected technician was not found.');
                        return;
                    }

                    if (!$user->hasRole(RoleCatalog::TECHNICIAN)) {
                        $fail('The selected user is not a technician.');
                    }

                    // Check availability
                    $data = request()->all();
                    $scheduledAt = $data['scheduled_at'] ?? null;

                    if (empty($scheduledAt)) {
                        return;
                    }

                    $estimatedHours = $data['estimated_hours'] ?? 1;
                    $scheduledDateTime = Carbon::parse($scheduledAt);
                    $endDateTime = $scheduledDateTime->copy()->addHours((float) $estimatedHours);

                    $conflictCount = DB::table('service_requests')
                        ->where('technician_id', $value)
                        ->whereNotIn('status', [
                            ServiceRequest::STATUS_COMPLETED,
                            ServiceRequest::STATUS_CANCELLED,
                        ])
                        ->where(function ($query) use ($scheduledDateTime, $endDateTime) {
                            $query->whereBetween('scheduled_at', [$scheduledDateTime, $endDateTime])
                                ->orWhere(function ($q) use ($scheduledDateTime, $endDateTime) {
                                    $q->where('scheduled_at', '<=', $scheduledDateTime)
                                        ->whereRaw('DATE_ADD(scheduled_at, INTERVAL COALESCE(estimated_hours, 1) HOUR) >= ?', [$scheduledDateTime]);
                                });
                        })
                        ->count();

                    if ($conflictCount > 0) {
                        $fail('The selected technician is not available at the scheduled time.');
                    }
                },
            ],
            'scheduled_at' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    if (empty($value)) {
                        return;
                    }

                    $scheduledDateTime = Carbon::parse($value);
                    $dayOfWeek = $scheduledDateTime->dayOfWeek;
                    $hour = $scheduledDateTime->hour;

                    // Check if it's a weekend
                    if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
                        $fail('Service requests cannot be scheduled on weekends.');
                        return;
                    }

                    // Check business hours (8am-6pm)
                    if ($hour < 8 || $hour >= 18) {
                        $fail('Service requests must be scheduled during business hours (8 AM - 6 PM).');
                    }
                },
            ],
        ];
    }

    /**
     * Prepare data before validation
     */
    public static function prepareForPipeline(): array
    {
        $data = request()->all();

        // Normalize priority to lowercase
        if (isset($data['priority'])) {
            $data['priority'] = strtolower($data['priority']);
        }

        return $data;
    }
}
