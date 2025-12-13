<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractorProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contractor_id' => ['required', 'integer', 'exists:contractors,contractor_id'],
            'project_title' => ['required', 'string', 'max:200'],
            'project_description' => ['required', 'string'],
            'project_location' => ['required', 'string', 'max:500'],
            // Media upload (photo/video) - optional
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi', 'max:10240'], // 10MB max per file
        ];
    }

    public function messages(): array
    {
        return [
            'project_title.required' => 'Header/Title is required.',
            'project_description.required' => 'Description is required.',
        ];
    }
}
