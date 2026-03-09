<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['required', 'integer', 'exists:property_owners,owner_id'],
            'project_title' => ['required', 'string', 'max:200'],
            'project_description' => ['required', 'string'],
            // Property Address fields
            'street_address' => ['required', 'string', 'max:255'],
            'city_municipality' => ['required', 'string', 'max:255'],
            'province_state_region' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            // Property Details
            'property_type' => ['required', 'in:Residential,Commercial,Industrial,Agricultural'],
            'lot_size' => ['required', 'integer', 'min:0'],
            'floor_area' => ['required', 'array', 'min:1'],
            'floor_area.*' => ['required', 'numeric', 'min:0'],
            // Target Timeline
            'timeline_min' => ['required', 'integer', 'min:1'],
            'timeline_max' => ['required', 'integer', 'min:1', 'gte:timeline_min'],
            // Budget
            'budget_range_min' => ['required', 'numeric', 'min:0'],
            'budget_range_max' => ['required', 'numeric', 'min:0', 'gte:budget_range_min'],
            // Bidding Deadline
            'bidding_deadline' => ['required', 'date', 'after:today'],
            // Contractor Type
            'type_id' => ['required', 'integer', 'exists:contractor_types,type_id'],
            'contractor_type_other' => ['nullable', 'string', 'max:200', 'required_if:type_id,9'],
            // File Uploads
            'house_photos' => ['nullable', 'array'],
            'house_photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // 10MB max per image
            'land_title' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'], // 10MB max
            'blueprint' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'], // 10MB max
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,zip', 'max:10240'], // 10MB max per file
        ];
    }

    public function messages(): array
    {
        return [
            'land_title.required' => 'Land title document is required for verification.',
            'timeline_max.gte' => 'Maximum timeline must be greater than or equal to minimum timeline.',
            'budget_range_max.gte' => 'Maximum budget must be greater than or equal to minimum budget.',
            'bidding_deadline.after' => 'Bidding deadline must be a future date.',
        ];
    }
}


