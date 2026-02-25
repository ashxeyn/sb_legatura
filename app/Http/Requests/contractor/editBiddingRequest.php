<?php

namespace App\Http\Requests\contractor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class editBiddingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bid_id' => 'nullable|integer|exists:bids,bid_id',
            'proposed_cost' => 'required|numeric|min:0',
            'estimated_timeline' => 'required',
            'contractor_notes' => 'nullable|string|max:5000',
            'bid_files.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,zip,rar,gif,xls,xlsx',
            'delete_files.*' => 'nullable|integer'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'bid_id.required' => 'Bid ID is required.',
            'bid_id.exists' => 'The selected bid does not exist.',
            'proposed_cost.required' => 'Proposed cost is required.',
            'proposed_cost.numeric' => 'Proposed cost must be a number.',
            'proposed_cost.min' => 'Proposed cost must be at least 0.',
            'estimated_timeline.required' => 'Estimated timeline is required.',
            'estimated_timeline.integer' => 'Estimated timeline must be a whole number.',
            'estimated_timeline.min' => 'Timeline must be at least 1 month.',
            'contractor_notes.max' => 'Notes cannot exceed 5000 characters.',
            'bid_files.*.max' => 'Each file cannot exceed 10MB.',
            'bid_files.*.mimes' => 'Files must be PDF, DOC, DOCX, JPG, JPEG, PNG, ZIP, or RAR.'
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
