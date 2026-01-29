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
        if ($this->routeIs('api.admin.contractor.suspend')) {
            return [
                'reason' => 'required|string',
                'duration' => 'required|in:temporary,permanent',
                'suspension_until' => 'nullable|date|after:today|required_if:duration,temporary',
            ];
        }

        $isUpdate = !empty($this->route('user_id'));
        $userId = $this->route('user_id');

        $rules = [
            // Company Information
            'company_name' => 'required|string|max:255',
            'company_phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'company_start_date' => 'required|date|before:today',
            'contractor_type_id' => 'required|exists:contractor_types,type_id',
            'contractor_type_other_text' => 'required_if:contractor_type_id,9|nullable|string|max:255',
            'services_offered' => 'nullable|string',
            'company_website' => 'nullable|url|max:255',
            'company_social_media' => 'nullable|url|max:255',

            // Business Address
            'business_address_street' => 'required|string|max:255',
            'business_address_barangay' => 'required|string|max:255',
            'business_address_city' => 'required|string|max:255',
            'business_address_province' => 'required|string|max:255',
            'business_address_postal' => 'required|string|max:10',

            // Representative/Contact
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',

            // Legal Documents (Files/Data)
            'picab_number' => 'required|string|max:50',
            'picab_category' => 'required|string|max:50',
            'picab_expiration_date' => 'required|date|after:today',
            'business_permit_number' => 'required|string|max:50',
            'business_permit_city' => 'required|string|max:100',
            'business_permit_expiration' => 'required|date|after:today',
            'tin_business_reg_number' => 'required|string|max:50',
        ];

        if ($isUpdate) {
            // Update Rules
            $rules['company_email'] = [
                'required',
                'email',
                'max:100',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId, 'user_id'),
                \Illuminate\Validation\Rule::unique('admin_users', 'email')
            ];
            $rules['password'] = 'nullable|min:8';

            // Files are optional on update
            $rules['profile_pic'] = 'nullable|image|max:5120';
            $rules['dti_sec_registration_photo'] = 'nullable|image|max:5120';
        } else {
            // Create Rules
            $rules['company_email'] = [
                'required',
                'email',
                'max:100',
                'unique:users,email',
                'unique:admin_users,email'
            ];

            // Files are required on create
            $rules['profile_pic'] = 'nullable|image|max:5120';
            $rules['dti_sec_registration_photo'] = 'required|image|max:5120';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'company_phone.regex' => 'Company phone number should be in Philippine format (e.g., 09123456789).',
            'picab_expiration_date.after' => 'PCAB expiration date must be a future date.',
            'business_permit_expiration.after' => 'Business permit expiration date must be a future date.',
            'contractor_type_other_text.required_if' => 'Please specify the contractor type.',
        ];
    }
}
