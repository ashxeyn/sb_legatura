<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class changeContractorRepresentativeRequest extends FormRequest
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
            'contractor_id' => 'required|exists:contractors,contractor_id',
            'new_representative_id' => 'required|exists:contractor_users,contractor_user_id',
        ];
    }

    public function messages()
    {
        return [
            'contractor_id.required' => 'Contractor ID is required.',
            'contractor_id.exists' => 'Invalid contractor.',
            'new_representative_id.required' => 'Please select a team member.',
            'new_representative_id.exists' => 'Selected team member does not exist.',
        ];
    }
}
