<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class editMilestoneRequest extends FormRequest
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
            'milestone_item_title' => 'required|string|max:200',
            'milestone_item_description' => 'required|string',
            'date_to_finish' => 'required|date',
            'milestone_item_cost' => 'required|numeric|min:0',
            'item_status' => 'required|in:pending,not_started,in_progress,delayed,completed,cancelled,halt,deleted'
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
            'milestone_item_title.required' => 'item title is required',
            'milestone_item_title.max' => 'item title must not exceed 200 characters',
            'milestone_item_description.required' => 'item description is required',
            'date_to_finish.required' => 'date to finish is required',
            'date_to_finish.date' => 'date to finish must be a valid date',
            'milestone_item_cost.required' => 'estimated cost is required',
            'milestone_item_cost.numeric' => 'estimated cost must be a number',
            'milestone_item_cost.min' => 'estimated cost must be at least 0',
            'item_status.required' => 'item status is required',
            'item_status.in' => 'item status must be a valid status'
        ];
    }
}
