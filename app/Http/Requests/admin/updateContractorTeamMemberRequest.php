<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class updateContractorTeamMemberRequest extends FormRequest
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
            // Role only - we're only updating the role in contractor_staff table
            'staff_id' => 'required|exists:contractor_staff,staff_id',
            'role' => 'required|in:manager,engineer,architect,representative,others',
            'role_other' => 'required_if:role,others|nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'staff_id.required' => 'Team member ID is required.',
            'staff_id.exists' => 'Team member not found.',
            'role.required' => 'Role is required.',
            'role.in' => 'Please select a valid role.',
            'role_other.required_if' => 'Please specify the role when "Others" is selected.',
        ];
    }
}
