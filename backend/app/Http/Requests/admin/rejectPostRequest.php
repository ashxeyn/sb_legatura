<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class rejectPostRequest extends FormRequest
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
            'reason' => 'required|string|min:10|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'A reason for rejection is required.',
            'reason.min' => 'The reason must be at least 10 characters.',
            'reason.max' => 'The reason must not exceed 500 characters.',
        ];
    }
}

