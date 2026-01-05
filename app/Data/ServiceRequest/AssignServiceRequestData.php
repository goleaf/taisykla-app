<?php

namespace App\Data\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Attributes\Validation\After;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Data object for assigning a technician to a service request.
 * 
 * Replaces AssignTechnicianRequest.
 */
class AssignServiceRequestData extends Data
{
    public function __construct(
        #[Required, Exists('users', 'id')]
        public int $technician_id,

        #[After('now')]
        public ?string $scheduled_at = null,

        #[Numeric]
        public ?float $estimated_hours = null,
    ) {
    }

    /**
     * Additional validation rules
     */
    public static function rules(...$args): array
    {
        return [
            'technician_id' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    $user = User::find($value);

                    if (!$user) {
                        $fail('The selected technician was not found.');
                        return;
                    }

                    if (!$user->hasRole(RoleCatalog::TECHNICIAN)) {
                        $fail('The selected user is not a technician.');
                        return;
                    }

                    // Check availability if scheduled_at is provided
                    $data = request()->all();
                    $scheduledAt = $data['scheduled_at'] ?? null;

                    if (empty($scheduledAt)) {
                        return;
                    }

                    $estimatedHours = $data['estimated_hours'] ?? 1;
                    $scheduledDateTime = Carbon::parse($scheduledAt);
                    $endDateTime = $scheduledDateTime->copy()->addHours((float) $estimatedHours);

                    // Get the service request ID from route parameter
                    $serviceRequestId = request()->route('service_request') ?? request()->route('id');

                    $query = DB::table('service_requests')
                        ->where('technician_id', $value)
                        ->whereNotIn('status', [
                            ServiceRequest::STATUS_COMPLETED,
                            ServiceRequest::STATUS_CANCELLED,
                        ])
                        ->where(function ($q) use ($scheduledDateTime, $endDateTime) {
                            $q->whereBetween('scheduled_at', [$scheduledDateTime, $endDateTime])
                                ->orWhere(function ($subQ) use ($scheduledDateTime) {
                                    $subQ->where('scheduled_at', '<=', $scheduledDateTime)
                                        ->whereRaw('DATE_ADD(scheduled_at, INTERVAL COALESCE(estimated_hours, 1) HOUR) >= ?', [$scheduledDateTime]);
                                });
                        });

                    // Exclude current service request if updating
                    if ($serviceRequestId) {
                        $query->where('id', '!=', $serviceRequestId);
                    }

                    if ($query->count() > 0) {
                        $fail('The selected technician is not available at the scheduled time.');
                    }
                },
            ],
        ];
    }
}
