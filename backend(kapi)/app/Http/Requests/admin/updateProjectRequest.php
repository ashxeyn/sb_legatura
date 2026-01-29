<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_title' => 'sometimes|required|string|max:200',
            'project_description' => 'sometimes|required|string',
            'project_location' => 'sometimes|required|string',
            'budget_range_min' => 'nullable|numeric|min:0',
            'budget_range_max' => 'nullable|numeric|min:0|gte:budget_range_min',
            'lot_size' => 'nullable|integer|min:0',
            'property_type' => 'nullable|string',
            'to_finish' => 'nullable|integer|min:0',
            'project_status' => 'nullable|in:open,bidding_closed,in_progress,completed,terminated',
            'selected_contractor_id' => 'nullable|integer|exists:contractors,contractor_id',
            'bidding_deadline' => 'nullable|date',
        ];
    }
}


