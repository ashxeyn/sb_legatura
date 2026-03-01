<?php

namespace App\Http\Requests\message;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check Bearer token (mobile), session (web), or X-User-Id header
        $bearerToken = $this->bearerToken();
        if ($bearerToken) {
            // Sanctum tokens: {id}|{plaintext} — hash only the plaintext
            $tokenParts = explode('|', $bearerToken, 2);
            $plainText = count($tokenParts) === 2 ? $tokenParts[1] : $bearerToken;
            $tokenHash = hash('sha256', $plainText);
            $exists = DB::table('personal_access_tokens')->where('token', $tokenHash)->exists();
            if ($exists)
                return true;
        }
        if (session()->has('user'))
            return true;
        if ($this->header('X-User-Id'))
            return true;
        return false;
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
        // Resolve sender_id from Bearer token (manual DB lookup) or session
        $userId = null;

        $bearerToken = $this->bearerToken();
        if ($bearerToken) {
            // Sanctum tokens: {id}|{plaintext} — hash only the plaintext
            $tokenParts = explode('|', $bearerToken, 2);
            $plainText = count($tokenParts) === 2 ? $tokenParts[1] : $bearerToken;
            $tokenHash = hash('sha256', $plainText);
            $tokenRecord = DB::table('personal_access_tokens')->where('token', $tokenHash)->first();
            if ($tokenRecord) {
                $userId = (int) $tokenRecord->tokenable_id;
            }
        }

        if (!$userId) {
            $sessionUser = session('user');
            if ($sessionUser) {
                $userId = $sessionUser->admin_id ?? $sessionUser->user_id ?? $sessionUser->id ?? null;
            }
        }

        if (!$userId) {
            $userId = $this->header('X-User-Id') ? (int) $this->header('X-User-Id') : null;
        }

        $this->merge([
            'sender_id' => $userId
        ]);
    }
}
