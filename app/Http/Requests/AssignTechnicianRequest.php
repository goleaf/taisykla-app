<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form Request for assigning a technician to a Service Request.
 *
 * Handles comprehensive validation including:
 * - Technician role verification
 * - Technician availability checking
 * - Scheduling validation with business hours
 * - Authorization for dispatch/operations roles
 */
class AssignTechnicianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('work_orders.assign') ||
            $this->user()->hasAnyRole([
                RoleCatalog::ADMIN,
                RoleCatalog::OPERATIONS_MANAGER,
                RoleCatalog::DISPATCH,
            ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'technician_id' => [
                'required',
                'integer',
                'exists:users,id',
                $this->validateTechnicianRole(),
                $this->validateTechnicianAvailability(),
            ],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
                $this->validateBusinessHours(),
            ],
            'estimated_hours' => [
                'required',
                'numeric',
                'min:0.5',
                'max:24',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Validate that the selected user has the technician role.
     */
    protected function validateTechnicianRole(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value)) {
                return;
            }

            $user = User::find($value);
            if (!$user) {
                $fail(__('validation.assign_technician.user_not_found'));
                return;
            }

            if (!$user->hasRole(RoleCatalog::TECHNICIAN)) {
                $fail(__('validation.assign_technician.user_not_technician'));
            }

            // Additional check: technician must be active
            if ($user->is_active === false) {
                $fail(__('validation.assign_technician.technician_inactive'));
            }
        };
    }

    /**
     * Validate technician availability for the scheduled time.
     */
    protected function validateTechnicianAvailability(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value)) {
                return;
            }

            $scheduledAt = $this->input('scheduled_at');
            $estimatedHours = $this->input('estimated_hours', 1);

            if (empty($scheduledAt)) {
                return;
            }

            $scheduledDateTime = Carbon::parse($scheduledAt);
            $endDateTime = $scheduledDateTime->copy()->addHours((float) $estimatedHours);

            // Get current service request being assigned
            $currentRequest = $this->route('service_request') ?? $this->route('serviceRequest');
            $currentRequestId = $currentRequest instanceof ServiceRequest ? $currentRequest->id : null;

            // Check for overlapping assignments
            $query = DB::table('service_requests')
                ->where('technician_id', $value)
                ->whereNotIn('status', [
                    ServiceRequest::STATUS_COMPLETED,
                    ServiceRequest::STATUS_CANCELLED,
                ])
                ->where(function ($q) use ($scheduledDateTime, $endDateTime) {
                    // Check for any overlap in time ranges
                    $q->where(function ($inner) use ($scheduledDateTime, $endDateTime) {
                        $inner->where('scheduled_at', '<', $endDateTime)
                            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL COALESCE(estimated_hours, 1) HOUR) > ?', [$scheduledDateTime]);
                    });
                });

            // Exclude current request if updating
            if ($currentRequestId) {
                $query->where('id', '!=', $currentRequestId);
            }

            $conflictingRequests = $query->count();

            if ($conflictingRequests > 0) {
                $fail(__('validation.assign_technician.technician_busy', [
                    'time' => $scheduledDateTime->format('Y-m-d H:i'),
                ]));
            }

            // Check technician's daily capacity
            $dailyAssignments = DB::table('service_requests')
                ->where('technician_id', $value)
                ->whereDate('scheduled_at', $scheduledDateTime->toDateString())
                ->whereNotIn('status', [
                    ServiceRequest::STATUS_COMPLETED,
                    ServiceRequest::STATUS_CANCELLED,
                ])
                ->when($currentRequestId, fn($q) => $q->where('id', '!=', $currentRequestId))
                ->sum('estimated_hours');

            $user = User::find($value);
            $maxDailyMinutes = $user->max_daily_minutes ?? 480; // Default 8 hours
            $availableMinutes = $maxDailyMinutes - ($dailyAssignments * 60);

            if (($estimatedHours * 60) > $availableMinutes) {
                $fail(__('validation.assign_technician.exceeds_capacity', [
                    'available' => round($availableMinutes / 60, 1),
                ]));
            }
        };
    }

    /**
     * Validate that scheduled time is during business hours.
     */
    protected function validateBusinessHours(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value)) {
                return;
            }

            $scheduledDateTime = Carbon::parse($value);
            $dayOfWeek = $scheduledDateTime->dayOfWeek;
            $hour = $scheduledDateTime->hour;

            if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
                $fail(__('validation.assign_technician.weekend_schedule'));
                return;
            }

            if ($hour < 8 || $hour >= 18) {
                $fail(__('validation.assign_technician.outside_business_hours'));
            }
        };
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'technician_id.required' => __('validation.assign_technician.technician_required'),
            'technician_id.exists' => __('validation.assign_technician.technician_not_found'),
            'scheduled_at.required' => __('validation.assign_technician.schedule_required'),
            'scheduled_at.date' => __('validation.assign_technician.schedule_invalid'),
            'scheduled_at.after' => __('validation.assign_technician.schedule_future'),
            'estimated_hours.required' => __('validation.assign_technician.hours_required'),
            'estimated_hours.numeric' => __('validation.assign_technician.hours_numeric'),
            'estimated_hours.min' => __('validation.assign_technician.hours_min'),
            'estimated_hours.max' => __('validation.assign_technician.hours_max'),
            'notes.max' => __('validation.assign_technician.notes_max'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'technician_id' => __('validation.attributes.technician'),
            'scheduled_at' => __('validation.attributes.scheduled_at'),
            'estimated_hours' => __('validation.attributes.estimated_hours'),
            'notes' => __('validation.attributes.notes'),
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // After validation passes, we can add the technician info
        // for use in the controller
        $technician = User::find($this->technician_id);
        $this->merge([
            'technician_name' => $technician?->name,
        ]);
    }
}
