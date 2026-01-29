<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBidRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_id' => 'required|integer|exists:projects,project_id',
            'contractor_id' => 'required|integer|exists:contractors,contractor_id',
            'proposed_cost' => 'required|numeric|min:0',
            'submitted_at' => 'nullable|date',
            'bid_status' => 'nullable|in:pending,approved,rejected'
        ];
    }
}
