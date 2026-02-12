<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class rejectVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Admin middleware handles auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:5|max:500',
            'targetRole' => 'required|string|in:contractor,property_owner',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'A rejection reason is required.',
            'reason.min' => 'The rejection reason must be at least 5 characters.',
            'targetRole.required' => 'The role being rejected is required.',
            'targetRole.in' => 'The role being rejected must be either contractor or property_owner.',
        ];
    }
}
