<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['sometimes', 'integer', 'exists:property_owners,owner_id'],
            'project_title' => ['sometimes', 'required', 'string', 'max:200'],
            'project_description' => ['sometimes', 'required', 'string'],
            'project_location' => ['sometimes', 'required', 'string'],
            'budget_range_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'budget_range_max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'lot_size' => ['sometimes', 'required', 'integer', 'min:0'],
            'property_type' => ['sometimes', 'required', 'in:Residential,Commercial,Industrial,Agricultural'],
            'type_id' => ['sometimes', 'required', 'integer', 'exists:contractor_types,type_id'],
            'to_finish' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'project_status' => ['sometimes', 'in:open,bidding_closed,in_progress,completed,terminated'],
            'selected_contractor_id' => ['sometimes', 'nullable', 'integer', 'exists:contractors,contractor_id'],
            'bidding_deadline' => ['sometimes', 'required', 'date'],
        ];
    }
}


