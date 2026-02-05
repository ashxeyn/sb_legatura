<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class haltProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dispute_id' => 'required|integer|exists:disputes,dispute_id',
            'halt_reason' => 'required|string|min:10|max:1000',
            'project_remarks' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dispute_id.required' => 'please select an associated halt dispute.',
            'dispute_id.exists' => 'the selected dispute is invalid.',
            'halt_reason.required' => 'halt reason is required.',
            'halt_reason.min' => 'halt reason must be at least 10 characters.',
            'halt_reason.max' => 'halt reason cannot exceed 1000 characters.',
            'project_remarks.max' => 'administrative remarks cannot exceed 2000 characters.',
        ];
    }
}
