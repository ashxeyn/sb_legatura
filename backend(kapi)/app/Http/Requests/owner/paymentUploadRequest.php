<?php

namespace App\Http\Requests\owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class paymentUploadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'item_id' => 'required|integer',
            'project_id' => 'required|integer',
            'amount' => 'required|numeric',
            'payment_type' => 'required|string',
            'transaction_number' => 'nullable|string|max:100',
            'receipt_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'transaction_date' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'item_id.required' => 'Milestone item is required.',
            'item_id.integer' => 'Invalid milestone item selected.',
            'project_id.required' => 'Project is required.',
            'project_id.integer' => 'Invalid project selected.',
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'payment_type.required' => 'Payment type is required.',
            'transaction_number.max' => 'Transaction number cannot exceed 100 characters.',
            'receipt_photo.file' => 'Receipt photo must be a valid file.',
            'receipt_photo.mimes' => 'Receipt photo must be JPG, JPEG, PNG, or PDF format.',
            'receipt_photo.max' => 'Receipt photo must not exceed 5MB.',
            'transaction_date.date' => 'Transaction date must be a valid date.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $isMobileRequest = $this->expectsJson();

        $response = [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ];

        if ($isMobileRequest) {
            $response['validation_errors'] = $validator->errors()->toArray();
            $response['message'] = 'Please check your input and try again';
        }

        throw new HttpResponseException(response()->json($response, 422));
    }
}
