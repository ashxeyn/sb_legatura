<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class reactivateContractorTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contractor_user_id' => 'required|integer', // Generic ID field for all types
            'user_type' => 'required|string|in:contractor,property_owner,staff',
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
            'contractor_user_id.required' => 'ID is required.',
            'contractor_user_id.integer' => 'ID must be an integer.',
            'user_type.required' => 'User type is required.',
            'user_type.in' => 'Invalid user type.',
        ];
    }
}
