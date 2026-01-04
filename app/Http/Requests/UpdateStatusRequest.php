<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest;
use App\Support\RoleCatalog;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request for updating a Service Request status.
 *
 * Handles comprehensive validation including:
 * - Valid status values
 * - Required fields based on status (notes for cancelled/completed, actual hours/cost for completed)
 * - Status transition validation
 * - Authorization based on role
 */
class UpdateStatusRequest extends FormRequest
{
    /**
     * Valid status transitions map.
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
            // Cannot transition from completed
        ],
        ServiceRequest::STATUS_CANCELLED => [
            ServiceRequest::STATUS_PENDING, // Can reopen
        ],
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Admin, operations, or dispatch can update any status
        if (
            $user->hasAnyRole([
                RoleCatalog::ADMIN,
                RoleCatalog::OPERATIONS_MANAGER,
                RoleCatalog::DISPATCH,
            ])
        ) {
            return true;
        }

        // Technician can update status if assigned to them
        $serviceRequest = $this->route('service_request') ?? $this->route('serviceRequest');

        if (
            $serviceRequest instanceof ServiceRequest &&
            $user->hasRole(RoleCatalog::TECHNICIAN) &&
            $serviceRequest->technician_id === $user->id
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
            'status' => [
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
            'notes' => [
                'nullable',
                'string',
                'max:2000',
                $this->validateNotesRequired(),
            ],
            'actual_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                $this->validateActualHoursRequired(),
            ],
            'actual_cost' => [
                'nullable',
                'numeric',
                'min:0',
                $this->validateActualCostRequired(),
            ],
            'completion_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'cancellation_reason' => [
                'nullable',
                'string',
                'max:1000',
                $this->validateCancellationReasonRequired(),
            ],
        ];
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

            // Same status transition is always allowed (no change)
            if ($currentStatus === $value) {
                return;
            }

            $allowedTransitions = $this->validStatusTransitions[$currentStatus] ?? [];

            if (!in_array($value, $allowedTransitions, true)) {
                $fail(__('validation.status.transition_not_allowed', [
                    'from' => $this->getStatusLabel($currentStatus),
                    'to' => $this->getStatusLabel($value),
                ]));
            }
        };
    }

    /**
     * Validate notes are required for cancelled or completed status.
     */
    protected function validateNotesRequired(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $status = $this->input('status');
            $cancellationReason = $this->input('cancellation_reason');
            $completionNotes = $this->input('completion_notes');

            if (
                $status === ServiceRequest::STATUS_CANCELLED &&
                empty($value) && empty($cancellationReason)
            ) {
                $fail(__('validation.status.notes_required_cancelled'));
            }

            if (
                $status === ServiceRequest::STATUS_COMPLETED &&
                empty($value) && empty($completionNotes)
            ) {
                $fail(__('validation.status.notes_required_completed'));
            }
        };
    }

    /**
     * Validate actual_hours is required when status is completed.
     */
    protected function validateActualHoursRequired(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (
                $this->input('status') === ServiceRequest::STATUS_COMPLETED &&
                (is_null($value) || $value === '')
            ) {
                $fail(__('validation.status.actual_hours_required'));
            }
        };
    }

    /**
     * Validate actual_cost is required when status is completed.
     */
    protected function validateActualCostRequired(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (
                $this->input('status') === ServiceRequest::STATUS_COMPLETED &&
                (is_null($value) || $value === '')
            ) {
                $fail(__('validation.status.actual_cost_required'));
            }
        };
    }

    /**
     * Validate cancellation_reason is required when status is cancelled.
     */
    protected function validateCancellationReasonRequired(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $status = $this->input('status');
            $notes = $this->input('notes');

            if (
                $status === ServiceRequest::STATUS_CANCELLED &&
                empty($value) && empty($notes)
            ) {
                $fail(__('validation.status.cancellation_reason_required'));
            }
        };
    }

    /**
     * Get human-readable status label.
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            ServiceRequest::STATUS_PENDING => __('statuses.pending'),
            ServiceRequest::STATUS_ASSIGNED => __('statuses.assigned'),
            ServiceRequest::STATUS_IN_PROGRESS => __('statuses.in_progress'),
            ServiceRequest::STATUS_COMPLETED => __('statuses.completed'),
            ServiceRequest::STATUS_CANCELLED => __('statuses.cancelled'),
            default => ucfirst(str_replace('_', ' ', $status)),
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
            'status.required' => __('validation.status.status_required'),
            'status.in' => __('validation.status.status_invalid'),
            'notes.max' => __('validation.status.notes_max'),
            'actual_hours.numeric' => __('validation.status.actual_hours_numeric'),
            'actual_hours.min' => __('validation.status.actual_hours_min'),
            'actual_hours.max' => __('validation.status.actual_hours_max'),
            'actual_cost.numeric' => __('validation.status.actual_cost_numeric'),
            'actual_cost.min' => __('validation.status.actual_cost_min'),
            'completion_notes.max' => __('validation.status.completion_notes_max'),
            'cancellation_reason.max' => __('validation.status.cancellation_reason_max'),
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
            'status' => __('validation.attributes.status'),
            'notes' => __('validation.attributes.notes'),
            'actual_hours' => __('validation.attributes.actual_hours'),
            'actual_cost' => __('validation.attributes.actual_cost'),
            'completion_notes' => __('validation.attributes.completion_notes'),
            'cancellation_reason' => __('validation.attributes.cancellation_reason'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->status),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $status = $this->input('status');

            // Add warning if completing without actual data
            if ($status === ServiceRequest::STATUS_COMPLETED) {
                $serviceRequest = $this->route('service_request') ?? $this->route('serviceRequest');

                if (
                    $serviceRequest instanceof ServiceRequest &&
                    !$serviceRequest->technician_id &&
                    !$this->input('actual_hours')
                ) {
                    $validator->errors()->add(
                        'warning',
                        __('validation.status.completing_without_technician')
                    );
                }
            }
        });
    }
}
