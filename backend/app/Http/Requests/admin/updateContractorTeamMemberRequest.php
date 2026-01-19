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
        $contractorUserId = $this->route('id') ?? $this->input('contractor_user_id');

        // Get the user_id for this contractor_user
        $contractorUser = \DB::table('contractor_users')
            ->where('contractor_user_id', $contractorUserId)
            ->first();

        $userId = $contractorUser->user_id ?? null;

        return [
            // Personal Information
            'contractor_user_id' => 'required|exists:contractor_users,contractor_user_id',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($userId, 'user_id')
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($userId, 'user_id')
            ],
            'password' => 'nullable|string|min:8',
            'phone_number' => ['required', 'string', 'regex:/^09\d{9}$/'],

            // Role
            'role' => 'required|in:owner,manager,engineer,architect,representative,others',
            'role_other' => 'required_if:role,others|nullable|string|max:255',

            // Profile Picture
            'profile_pic' => 'nullable|image|max:5120', // 5MB max
        ];
    }

    public function messages()
    {
        return [
            'contractor_user_id.required' => 'Team member ID is required.',
            'contractor_user_id.exists' => 'Team member not found.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.min' => 'Password must be at least 8 characters.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must be in Philippine format (e.g., 09123456789).',
            'role.required' => 'Role is required.',
            'role.in' => 'Please select a valid role.',
            'role_other.required_if' => 'Please specify the role when "Others" is selected.',
            'profile_pic.image' => 'Profile picture must be an image.',
            'profile_pic.max' => 'Profile picture must not exceed 5MB.',
        ];
    }
}
