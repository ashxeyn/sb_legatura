<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string'
        ];
    }
}
