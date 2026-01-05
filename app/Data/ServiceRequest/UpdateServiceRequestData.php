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
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Data object for updating existing Service Requests.
 * 
 * Replaces UpdateServiceRequestRequest with built-in validation.
 */
class UpdateServiceRequestData extends Data
{
    public function __construct(
        #[Exists('organizations', 'id')]
        public int|Optional $customer_id,

        #[Exists('equipment', 'id')]
        public int|null|Optional $equipment_id,

        #[Exists('users', 'id')]
        public int|null|Optional $technician_id,

        #[In([ServiceRequest::PRIORITY_LOW, ServiceRequest::PRIORITY_MEDIUM, ServiceRequest::PRIORITY_HIGH, ServiceRequest::PRIORITY_URGENT])]
        public string|Optional $priority,

        #[In([ServiceRequest::STATUS_PENDING, ServiceRequest::STATUS_ASSIGNED, ServiceRequest::STATUS_IN_PROGRESS, ServiceRequest::STATUS_COMPLETED, ServiceRequest::STATUS_CANCELLED])]
        public string|Optional $status,

        #[Min(10), Max(5000)]
        public string|Optional $description,

        #[After('now')]
        public string|null|Optional $scheduled_at,

        #[Numeric, Min(0.5), Max(100)]
        public float|null|Optional $estimated_hours,

        #[Numeric, Min(0)]
        public float|null|Optional $actual_hours,

        #[Numeric, Min(0)]
        public float|null|Optional $estimated_cost,

        #[Numeric, Min(0)]
        public float|null|Optional $actual_cost,

        #[Max(2000)]
        public string|null|Optional $customer_notes,

        #[Max(2000)]
        public string|null|Optional $technician_notes,

        #[Max(2000)]
        public string|null|Optional $internal_notes,
    ) {
    }

    /**
     * Additional validation rules
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

                    if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
                        $fail('Service requests cannot be scheduled on weekends.');
                        return;
                    }

                    if ($hour < 8 || $hour >= 18) {
                        $fail('Service requests must be scheduled during business hours (8 AM - 6 PM).');
                    }
                },
            ],
        ];
    }
}
