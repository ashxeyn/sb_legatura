<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class contractorRequest extends FormRequest
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
            // User fields
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username|min:4',
            'password' => 'required|string|min:8',
            'contact_number' => 'required|string|max:20',

            // Contractor fields
            'company_name' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0',
            'type_id' => 'required|exists:contractor_types,type_id', // Assuming contractor_types table exists
            'contractor_type_other' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',

            // Address fields (if applicable)
            'street_address' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:10',
        ];
    }
}
