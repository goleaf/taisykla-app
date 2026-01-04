<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                Rule::in([
                    ServiceRequest::STATUS_PENDING,
                    ServiceRequest::STATUS_ASSIGNED,
                    ServiceRequest::STATUS_IN_PROGRESS,
                    ServiceRequest::STATUS_COMPLETED,
                    ServiceRequest::STATUS_CANCELLED,
                ])
            ],
        ];
    }
}
