<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBidRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'proposed_cost' => 'nullable|numeric|min:0',
            'submitted_at' => 'nullable|date',
            'bid_status' => 'nullable|in:pending,approved,rejected'
        ];
    }
}
