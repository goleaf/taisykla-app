<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the Policy/Controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'priority' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    ServiceRequest::PRIORITY_LOW,
                    ServiceRequest::PRIORITY_MEDIUM,
                    ServiceRequest::PRIORITY_HIGH,
                    ServiceRequest::PRIORITY_URGENT,
                ])
            ],
            'description' => ['sometimes', 'required', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'technician_notes' => ['nullable', 'string', 'max:1000'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
