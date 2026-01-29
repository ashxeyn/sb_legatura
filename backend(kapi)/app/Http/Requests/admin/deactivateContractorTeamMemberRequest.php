<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class deactivateContractorTeamMemberRequest extends FormRequest
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
            'contractor_user_id' => 'required|exists:contractor_users,contractor_user_id',
            'deletion_reason' => 'required|string|min:10',
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
            'contractor_user_id.required' => 'Team member ID is required.',
            'contractor_user_id.exists' => 'Team member not found.',
            'deletion_reason.required' => 'Deactivation reason is required.',
            'deletion_reason.min' => 'Reason must be at least 10 characters.',
        ];
    }
}
