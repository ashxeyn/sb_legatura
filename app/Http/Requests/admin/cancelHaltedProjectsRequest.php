<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class cancelHaltedProjectsRequest extends FormRequest
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
            'remarks' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'remarks.required' => 'A reason for termination is required.',
            'remarks.min' => 'The reason must be at least 10 characters.',
            'remarks.max' => 'The reason must not exceed 1000 characters.',
        ];
    }
}
