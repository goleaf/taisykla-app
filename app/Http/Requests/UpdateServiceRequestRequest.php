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
 * Form Request for updating an existing Service Request.
 *
 * Handles comprehensive validation including:
 * - Customer cannot be changed
 * - Equipment ownership validation
 * - Status transition validation
 * - Business hours scheduling validation
 * - Authorization based on ownership or admin/manager role
 */
class UpdateServiceRequestRequest extends FormRequest
{
    /**
     * Valid status transitions map.
     * Key is current status, value is array of allowed next statuses.
     */
    protected array $validStatusTransitions = [
        ServiceRequest::STATUS_PENDING => [
            ServiceRequest::STATUS_ASSIGNED,
            ServiceRequest::STATUS_IN_PROGRESS,
            ServiceRequest::STATUS_CANCELLED,
        ],
        ServiceRequest::STATUS_ASSIGNED => [
            ServiceRequest::STATUS_PENDING,
            ServiceRequest::STATUS_IN_PROGRESS,
            ServiceRequest::STATUS_CANCELLED,
        ],
        ServiceRequest::STATUS_IN_PROGRESS => [
            ServiceRequest::STATUS_ASSIGNED,
            ServiceRequest::STATUS_COMPLETED,
            ServiceRequest::STATUS_CANCELLED,
        ],
        ServiceRequest::STATUS_COMPLETED => [
            // Cannot transition from completed to other statuses
        ],
        ServiceRequest::STATUS_CANCELLED => [
            ServiceRequest::STATUS_PENDING, // Can reopen a cancelled request
        ],
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var ServiceRequest|null $serviceRequest */
        $serviceRequest = $this->route('service_request') ?? $this->route('serviceRequest');

        if (!$serviceRequest instanceof ServiceRequest) {
            return false;
        }

        $user = $this->user();

        // Admin or operations manager can update any request
        if (
            $user->hasAnyRole([
                RoleCatalog::ADMIN,
                RoleCatalog::OPERATIONS_MANAGER,
                RoleCatalog::DISPATCH,
            ])
        ) {
            return true;
        }

        // Technician can update if assigned to them
        if (
            $user->hasRole(RoleCatalog::TECHNICIAN) &&
            $serviceRequest->technician_id === $user->id
        ) {
            return true;
        }

        // Customer can update their own requests (limited fields)
        if (
            $user->organization_id &&
            $serviceRequest->customer_id === $user->organization_id &&
            $serviceRequest->status === ServiceRequest::STATUS_PENDING
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // customer_id is optional but cannot be changed to a different customer
            'customer_id' => [
                'sometimes',
                'integer',
                'exists:organizations,id',
                $this->validateCustomerNotChanged(),
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
            ],
            'priority' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    ServiceRequest::PRIORITY_LOW,
                    ServiceRequest::PRIORITY_MEDIUM,
                    ServiceRequest::PRIORITY_HIGH,
                    ServiceRequest::PRIORITY_URGENT,
                ]),
            ],
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    ServiceRequest::STATUS_PENDING,
                    ServiceRequest::STATUS_ASSIGNED,
                    ServiceRequest::STATUS_IN_PROGRESS,
                    ServiceRequest::STATUS_COMPLETED,
                    ServiceRequest::STATUS_CANCELLED,
                ]),
                $this->validateStatusTransition(),
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'scheduled_at' => [
                'nullable',
                'date',
                $this->validateBusinessHours(),
            ],
            'estimated_hours' => [
                'nullable',
                'numeric',
                'min:0.5',
                'max:100',
            ],
            'actual_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'estimated_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'actual_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'customer_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'technician_notes' => [
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
     * Validate that customer cannot be changed to a different customer.
     */
    protected function validateCustomerNotChanged(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $serviceRequest = $this->route('service_request') ?? $this->route('serviceRequest');

            if (!$serviceRequest instanceof ServiceRequest) {
                return;
            }

            if (
                $serviceRequest->customer_id !== null &&
                (int) $value !== $serviceRequest->customer_id
            ) {
                $fail(__('validation.service_request.customer_cannot_change'));
            }
        };
    }

    /**
     * Validate status transition is allowed.
     */
    protected function validateStatusTransition(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $serviceRequest = $this->route('service_request') ?? $this->route('serviceRequest');

            if (!$serviceRequest instanceof ServiceRequest) {
                return;
            }

            $currentStatus = $serviceRequest->status;

            // If status hasn't changed, no validation needed
            if ($currentStatus === $value) {
                return;
            }

            $allowedTransitions = $this->validStatusTransitions[$currentStatus] ?? [];

            if (!in_array($value, $allowedTransitions, true)) {
                $fail(__('validation.service_request.status_transition_invalid', [
                    'from' => ucfirst(str_replace('_', ' ', $currentStatus)),
                    'to' => ucfirst(str_replace('_', ' ', $value)),
                ]));
            }
        };
    }

    /**
     * Custom validation to ensure equipment belongs to the service request's customer.
     */
    protected function validateEquipmentBelongsToCustomer(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value)) {
                return;
            }

            $serviceRequest = $this->route('service_request') ?? $this->route('serviceRequest');
            $customerId = $this->input('customer_id');

            if ($serviceRequest instanceof ServiceRequest && empty($customerId)) {
                $customerId = $serviceRequest->customer_id;
            }

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
     * Custom validation to ensure scheduled_at is during business hours.
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
                $fail(__('validation.business_hours_weekend'));
                return;
            }

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
            'customer_id.exists' => __('validation.service_request.customer_not_found'),
            'equipment_id.exists' => __('validation.service_request.equipment_not_found'),
            'technician_id.exists' => __('validation.service_request.technician_not_found'),
            'priority.required' => __('validation.service_request.priority_required'),
            'priority.in' => __('validation.service_request.priority_invalid'),
            'status.required' => __('validation.service_request.status_required'),
            'status.in' => __('validation.service_request.status_invalid'),
            'description.required' => __('validation.service_request.description_required'),
            'description.min' => __('validation.service_request.description_min'),
            'description.max' => __('validation.service_request.description_max'),
            'scheduled_at.date' => __('validation.service_request.scheduled_at_invalid'),
            'estimated_hours.numeric' => __('validation.service_request.estimated_hours_numeric'),
            'estimated_hours.min' => __('validation.service_request.estimated_hours_min'),
            'estimated_hours.max' => __('validation.service_request.estimated_hours_max'),
            'actual_hours.numeric' => __('validation.service_request.actual_hours_numeric'),
            'actual_hours.min' => __('validation.service_request.actual_hours_min'),
            'actual_hours.max' => __('validation.service_request.actual_hours_max'),
            'estimated_cost.numeric' => __('validation.service_request.estimated_cost_numeric'),
            'estimated_cost.min' => __('validation.service_request.estimated_cost_min'),
            'actual_cost.numeric' => __('validation.service_request.actual_cost_numeric'),
            'actual_cost.min' => __('validation.service_request.actual_cost_min'),
            'customer_notes.max' => __('validation.service_request.customer_notes_max'),
            'technician_notes.max' => __('validation.service_request.technician_notes_max'),
            'internal_notes.max' => __('validation.service_request.internal_notes_max'),
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
            'status' => __('validation.attributes.status'),
            'description' => __('validation.attributes.description'),
            'scheduled_at' => __('validation.attributes.scheduled_at'),
            'estimated_hours' => __('validation.attributes.estimated_hours'),
            'actual_hours' => __('validation.attributes.actual_hours'),
            'estimated_cost' => __('validation.attributes.estimated_cost'),
            'actual_cost' => __('validation.attributes.actual_cost'),
            'customer_notes' => __('validation.attributes.customer_notes'),
            'technician_notes' => __('validation.attributes.technician_notes'),
            'internal_notes' => __('validation.attributes.internal_notes'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('priority')) {
            $this->merge([
                'priority' => strtolower($this->priority),
            ]);
        }

        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->status),
            ]);
        }
    }
}
