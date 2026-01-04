<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\RoleCatalog;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form Request for creating a new Service Request.
 *
 * Handles comprehensive validation including:
 * - Customer existence and role verification
 * - Equipment ownership validation
 * - Business hours scheduling validation
 * - Technician availability checks
 */
class StoreServiceRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-service-requests') ||
            $this->user()->can('work_orders.create') ||
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
            'customer_id' => [
                'required',
                'integer',
                'exists:organizations,id',
            ],
            'equipment_id' => [
                'nullable',
                'integer',
                'exists:equipment,id',
                $this->validateEquipmentBelongsToCustomer(),
            ],
            'technician_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                $this->validateTechnicianRole(),
                $this->validateTechnicianAvailability(),
            ],
            'priority' => [
                'required',
                'string',
                Rule::in([
                    ServiceRequest::PRIORITY_LOW,
                    ServiceRequest::PRIORITY_MEDIUM,
                    ServiceRequest::PRIORITY_HIGH,
                    ServiceRequest::PRIORITY_URGENT,
                ]),
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'scheduled_at' => [
                'nullable',
                'date',
                'after:now',
                $this->validateBusinessHours(),
            ],
            'estimated_hours' => [
                'nullable',
                'numeric',
                'min:0.5',
                'max:100',
            ],
            'estimated_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'customer_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'internal_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Custom validation to ensure equipment belongs to the selected customer.
     */
    protected function validateEquipmentBelongsToCustomer(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value)) {
                return;
            }

            $customerId = $this->input('customer_id');
            if (empty($customerId)) {
                return;
            }

            $equipment = Equipment::find($value);
            if ($equipment && $equipment->customer_id !== (int) $customerId) {
                $fail(__('validation.equipment_customer_mismatch'));
            }
        };
    }

    /**
     * Custom validation to ensure the user has technician role.
     */
    protected function validateTechnicianRole(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value)) {
                return;
            }

            $user = User::find($value);
            if (!$user) {
                $fail(__('validation.technician_not_found'));
                return;
            }

            if (!$user->hasRole(RoleCatalog::TECHNICIAN)) {
                $fail(__('validation.user_not_technician'));
            }
        };
    }

    /**
     * Custom validation to check technician availability.
     */
    protected function validateTechnicianAvailability(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value)) {
                return;
            }

            $scheduledAt = $this->input('scheduled_at');
            if (empty($scheduledAt)) {
                return;
            }

            $estimatedHours = $this->input('estimated_hours', 1);
            $scheduledDateTime = Carbon::parse($scheduledAt);
            $endDateTime = $scheduledDateTime->copy()->addHours((float) $estimatedHours);

            // Check if technician has conflicting appointments
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
                $fail(__('validation.technician_unavailable'));
            }
        };
    }

    /**
     * Custom validation to ensure scheduled_at is during business hours.
     * Business hours: Monday-Friday, 8am-6pm
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

            // Check if it's a weekend (Saturday = 6, Sunday = 0)
            if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
                $fail(__('validation.business_hours_weekend'));
                return;
            }

            // Check if it's within business hours (8am-6pm)
            if ($hour < 8 || $hour >= 18) {
                $fail(__('validation.business_hours_time'));
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
            'customer_id.required' => __('validation.service_request.customer_required'),
            'customer_id.exists' => __('validation.service_request.customer_not_found'),
            'equipment_id.exists' => __('validation.service_request.equipment_not_found'),
            'technician_id.exists' => __('validation.service_request.technician_not_found'),
            'priority.required' => __('validation.service_request.priority_required'),
            'priority.in' => __('validation.service_request.priority_invalid'),
            'description.required' => __('validation.service_request.description_required'),
            'description.min' => __('validation.service_request.description_min'),
            'description.max' => __('validation.service_request.description_max'),
            'scheduled_at.date' => __('validation.service_request.scheduled_at_invalid'),
            'scheduled_at.after' => __('validation.service_request.scheduled_at_future'),
            'estimated_hours.numeric' => __('validation.service_request.estimated_hours_numeric'),
            'estimated_hours.min' => __('validation.service_request.estimated_hours_min'),
            'estimated_hours.max' => __('validation.service_request.estimated_hours_max'),
            'estimated_cost.numeric' => __('validation.service_request.estimated_cost_numeric'),
            'estimated_cost.min' => __('validation.service_request.estimated_cost_min'),
            'customer_notes.max' => __('validation.service_request.customer_notes_max'),
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
            'customer_id' => __('validation.attributes.customer'),
            'equipment_id' => __('validation.attributes.equipment'),
            'technician_id' => __('validation.attributes.technician'),
            'priority' => __('validation.attributes.priority'),
            'description' => __('validation.attributes.description'),
            'scheduled_at' => __('validation.attributes.scheduled_at'),
            'estimated_hours' => __('validation.attributes.estimated_hours'),
            'estimated_cost' => __('validation.attributes.estimated_cost'),
            'customer_notes' => __('validation.attributes.customer_notes'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize priority to lowercase
        if ($this->has('priority')) {
            $this->merge([
                'priority' => strtolower($this->priority),
            ]);
        }
    }
}
