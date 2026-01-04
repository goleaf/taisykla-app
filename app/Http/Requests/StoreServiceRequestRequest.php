<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequestRequest extends FormRequest
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
            'customer_id' => ['required', 'integer', 'exists:organizations,id'],
            'equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'priority' => [
                'required',
                'string',
                Rule::in([
                    ServiceRequest::PRIORITY_LOW,
                    ServiceRequest::PRIORITY_MEDIUM,
                    ServiceRequest::PRIORITY_HIGH,
                    ServiceRequest::PRIORITY_URGENT,
                ])
            ],
            'description' => ['required', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
