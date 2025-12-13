<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'relationship_id' => 'nullable|integer|exists:project_relationships,rel_id',
            'project_title' => 'required|string|max:200',
            'project_description' => 'required|string',
            'project_location' => 'required|string',
            'budget_range_min' => 'nullable|numeric|min:0',
            'budget_range_max' => 'nullable|numeric|min:0|gte:budget_range_min',
            'lot_size' => 'nullable|integer|min:0',
            'property_type' => 'required|string',
            'type_id' => 'nullable|integer',
            'to_finish' => 'nullable|integer|min:0',
            'project_status' => 'nullable|in:open,bidding_closed,in_progress,completed,terminated',
            'selected_contractor_id' => 'nullable|integer|exists:contractors,contractor_id',
            'bidding_deadline' => 'nullable|date',
        ];
    }
}

