<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'milestone_id' => 'nullable|integer|exists:milestones,milestone_id',
            'project_id' => 'required|integer|exists:projects,project_id',
            'payer_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'nullable|date',
            'payment_status' => 'nullable|in:pending,approved,rejected',
        ];
    }
}
