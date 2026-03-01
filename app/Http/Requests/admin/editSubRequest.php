<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class editSubRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust as needed for admin auth
    }

    public function rules()
    {
        return [
            'edit_subscription_name' => 'required|string|max:255',
            'edit_subscription_price' => 'required|numeric|min:0',
            'edit_billing_cycle' => 'required|string|in:monthly,quarterly,yearly,one-time',
            'edit_duration_days' => 'nullable|integer|min:1|required_if:edit_billing_cycle,one-time',
            'benefits' => 'required|array|min:1',
            'benefits.*' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'edit_subscription_name.required' => 'Subscription name is required.',
            'edit_subscription_price.required' => 'Price is required.',
            'edit_subscription_price.numeric' => 'Price must be a number.',
            'edit_billing_cycle.required' => 'Billing cycle is required.',
            'benefits.required' => 'At least one benefit is required.',
            'benefits.min' => 'At least one benefit is required.',
        ];
    }
}
