<?php

namespace App\Http\Requests\message;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check both Laravel auth (Sanctum) and session (admin web dashboard)
        return auth()->check() || session()->has('user');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'receiver_id' => 'required|integer|exists:users,user_id',
            'content' => [
                'nullable',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    // Content is required if no attachments
                    if (empty($value) && !$this->hasFile('attachments')) {
                        $fail('Please provide either message content or attachments.');
                    }
                }
            ],
            'conversation_id' => 'nullable|integer',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:10240' // 10MB max per file
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Please specify a message recipient.',
            'receiver_id.exists' => 'The selected recipient does not exist.',
            'content.required' => 'Message content cannot be empty.',
            'content.max' => 'Message content cannot exceed 5000 characters.',
            'attachments.max' => 'You can only upload up to 5 files per message.',
            'attachments.*.mimes' => 'Attachments must be: jpg, jpeg, png, pdf, doc, docx, or txt files.',
            'attachments.*.max' => 'Each attachment must not exceed 10MB.'
        ];
    }

    /**
     * Get custom attribute names for validation errors
     */
    public function attributes(): array
    {
        return [
            'receiver_id' => 'recipient',
            'content' => 'message',
            'attachments.*' => 'attachment'
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Ensure sender_id is set from authenticated user (Sanctum or session)
        $userId = auth()->id();
        if (!$userId) {
            $sessionUser = session('user');
            $userId = $sessionUser->admin_id ?? $sessionUser->user_id ?? $sessionUser->id ?? null;
        }

        $this->merge([
            'sender_id' => $userId
        ]);
    }
}
