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

    protected function prepareForValidation()
    {
        $data = [];
        // Representative fields removed from Add Contractor form.
        // No special normalization required for contractor request.
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

        // Determine whether this is an update (PUT/PATCH) or create (POST)
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || !empty($this->route('user_id'));

        // Create (strict) rules
        $createRules = [
            // Company Information
            'company_name' => 'required|string|max:255',
            'company_start_date' => 'required|date|before:today',
            'contractor_type_id' => 'required|exists:contractor_types,type_id',
            'contractor_type_other_text' => 'required_if:contractor_type_id,9|nullable|string|max:255',
            'services_offered' => 'required|string',
            'company_website' => 'nullable|url|max:255',
            'company_social_media' => 'nullable|url|max:255',

            // Business Address
            'business_address_street' => 'required|string|max:255',
            'business_address_barangay' => 'required|string|max:255',
            'business_address_city' => 'required|string|max:255',
            'business_address_province' => 'required|string|max:255',
            'business_address_postal' => 'required|string|max:10',

            // Legal Documents (Files/Data)
            'picab_number' => 'required|string|max:50',
            'picab_category' => 'required|string|max:50',
            'picab_expiration_date' => 'required|date|after:today',
            'business_permit_number' => 'required|string|max:50',
            'business_permit_city' => 'required|string|max:100',
            'business_permit_expiration' => 'required|date|after:today',
            'tin_business_reg_number' => 'required|string|max:50',

            // Files
            'profile_pic' => 'nullable|image|max:5120',
            'dti_sec_registration_photo' => 'required|image|max:5120',

            // Owner linking
            'owner_id' => 'required|exists:property_owners,owner_id',

            // Company email
            'company_email' => ['required', 'email', 'max:100'],
        ];

        // Update (lenient) rules — fields are optional; validate only when present
        $updateRules = [
            'company_name' => 'nullable|string|max:255',
            'company_start_date' => 'nullable|date|before:today',
            'contractor_type_id' => 'nullable|exists:contractor_types,type_id',
            'contractor_type_other_text' => 'nullable|string|max:255',
            'services_offered' => 'nullable|string',
            'company_website' => 'nullable|url|max:255',
            'company_social_media' => 'nullable|url|max:255',

            'business_address_street' => 'nullable|string|max:255',
            'business_address_barangay' => 'nullable|string|max:255',
            'business_address_city' => 'nullable|string|max:255',
            'business_address_province' => 'nullable|string|max:255',
            'business_address_postal' => 'nullable|string|max:10',

            'picab_number' => 'nullable|string|max:50',
            'picab_category' => 'nullable|string|max:50',
            'picab_expiration_date' => 'nullable|date|after:today',
            'business_permit_number' => 'nullable|string|max:50',
            'business_permit_city' => 'nullable|string|max:100',
            'business_permit_expiration' => 'nullable|date|after:today',
            'tin_business_reg_number' => 'nullable|string|max:50',

            'profile_pic' => 'nullable|image|max:5120',
            'dti_sec_registration_photo' => 'nullable|image|max:5120',

            // Email/password optional on update
            'company_email' => 'nullable|email|max:100',
            'password' => 'nullable|min:8',
        ];

        return $isUpdate ? $updateRules : $createRules;
    }

    public function messages()
    {
        return [
            'picab_expiration_date.after' => 'PCAB expiration date must be a future date.',
            'business_permit_expiration.after' => 'Business permit expiration date must be a future date.',
            'contractor_type_other_text.required_if' => 'Please specify the contractor type.',
        ];
    }
}
