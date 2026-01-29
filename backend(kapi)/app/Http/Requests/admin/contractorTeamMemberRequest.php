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
            // Personal Information
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'phone_number' => ['required', 'string', 'regex:/^09\d{9}$/'],

            // Role
            'role' => 'required|in:owner,manager,engineer,architect,representative,others',
            'role_other' => 'required_if:role,others|nullable|string|max:255',

            // Profile Picture
            'profile_pic' => 'nullable|image|max:5120', // 5MB max

            // Contractor ID
            'contractor_id' => 'required|exists:contractors,contractor_id'
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must be in Philippine format (e.g., 09123456789).',
            'role.required' => 'Role is required.',
            'role.in' => 'Please select a valid role.',
            'role_other.required_if' => 'Please specify the role when "Others" is selected.',
            'contractor_id.required' => 'Contractor ID is required.',
            'contractor_id.exists' => 'Invalid contractor.',
            'profile_pic.image' => 'Profile picture must be an image.',
            'profile_pic.max' => 'Profile picture must not exceed 5MB.',
        ];
    }
}
