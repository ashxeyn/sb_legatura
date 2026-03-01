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
            'plan_key' => 'required|string|max:255',
            'for_contractor' => 'required|boolean',
            'benefits' => 'required|array|min:1',
            'benefits.*' => 'required|string|max:500',
        ];
    }
}
