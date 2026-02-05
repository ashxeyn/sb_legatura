<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class deleteProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reason' => 'required|string|min:10|max:500'
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => 'deletion reason is required',
            'reason.min' => 'deletion reason must be at least 10 characters',
            'reason.max' => 'deletion reason cannot exceed 500 characters'
        ];
    }
}
