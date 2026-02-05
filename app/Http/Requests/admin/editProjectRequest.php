<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class editProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_title' => 'required|string|max:200',
            'project_description' => 'required|string',
            'property_type' => 'required|in:Residential,Commercial,Industrial,Agricultural',
            'lot_size' => 'required|integer|min:1',
            'floor_area' => 'required|integer|min:1',
            'project_location' => 'required|string',
            'selected_contractor_id' => 'nullable|integer|exists:contractors,contractor_id'
        ];
    }

    public function messages()
    {
        return [
            'project_title.required' => 'project title is required',
            'project_title.max' => 'project title cannot exceed 200 characters',
            'project_description.required' => 'project description is required',
            'property_type.required' => 'property type is required',
            'property_type.in' => 'invalid property type selected',
            'lot_size.required' => 'lot size is required',
            'lot_size.integer' => 'lot size must be a number',
            'lot_size.min' => 'lot size must be at least 1',
            'floor_area.required' => 'floor area is required',
            'floor_area.integer' => 'floor area must be a number',
            'floor_area.min' => 'floor area must be at least 1',
            'project_location.required' => 'project location is required',
            'selected_contractor_id.exists' => 'selected contractor does not exist'
        ];
    }
}
