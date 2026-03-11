<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class contractorTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all admins to use this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Property Owner Selection
            'owner_id' => 'required|exists:property_owners,owner_id',

            // Role
            'role' => 'required|in:manager,engineer,architect,representative,others',
            'role_other' => 'required_if:role,others|nullable|string|max:255',

            // Contractor ID
            'contractor_id' => 'required|exists:contractors,contractor_id'
        ];
    }

    public function messages()
    {
        return [
            'owner_id.required' => 'Please select a property owner.',
            'owner_id.exists' => 'Selected property owner not found.',
            'role.required' => 'Role is required.',
            'role.in' => 'Please select a valid role.',
            'role_other.required_if' => 'Please specify the role when "Others" is selected.',
            'contractor_id.required' => 'Contractor ID is required.',
            'contractor_id.exists' => 'Invalid contractor.',
        ];
    }
}
