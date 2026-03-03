<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class addSubRequest extends FormRequest
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
            'subscription_name' => 'required|string|max:255',
            'subscription_price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|string|in:monthly,yearly,quarterly,one-time',
            'duration_days' => 'nullable|integer|min:1|required_if:billing_cycle,one-time',
            'plan_key' => 'required|string|max:255|unique:subscription_plans,plan_key',
            'for_contractor' => 'required|boolean',
            'benefits' => 'required|array|min:1',
            'benefits.*' => 'required|string|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'subscription_name.required' => 'Subscription name is required.',
            'subscription_price.required' => 'Price is required.',
            'subscription_price.numeric' => 'Price must be a valid number.',
            'plan_key.required' => 'Plan key is required.',
            'plan_key.unique' => 'This plan key already exists. Please use a different plan key.',
            'billing_cycle.required' => 'Billing cycle is required.',
            'billing_cycle.in' => 'Invalid billing cycle selected.',
            'duration_days.required_if' => 'Duration days is required for one-time billing.',
            'for_contractor.required' => 'Please specify if this plan is for contractors.',
            'benefits.required' => 'At least one benefit is required.',
            'benefits.min' => 'Please add at least one benefit.',
        ];
    }
}
