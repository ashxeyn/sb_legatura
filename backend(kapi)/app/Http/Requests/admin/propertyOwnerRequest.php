<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class propertyOwnerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($this->routeIs('api.admin.propertyOwner.suspend')) {
            return [
                'reason' => 'required|string',
                'duration' => 'required|in:temporary,permanent',
                'suspension_until' => 'nullable|date|after:today|required_if:duration,temporary',
            ];
        }

        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $userId = $this->input('user_id');

        $rules = [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:-15 years',
            'phone_number' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'occupation_id' => 'required',
            'occupation_other' => 'required_if:occupation_id,others|nullable|string|max:200',

            'province' => 'required|string',
            'city' => 'required|string',
            'barangay' => 'required|string',
            'street_address' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',

            'valid_id_id' => 'required|exists:valid_ids,id',
            'profile_pic' => 'nullable|image|max:5120',
        ];

        if ($isUpdate) {
            // Update Rules
            $rules['user_id'] = 'required|exists:users,user_id';
            $rules['email'] = [
                'required',
                'email',
                'regex:/(.+)@(.+)\.(.+)/i',
                'max:100',
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
                Rule::unique('admin_users', 'email'),
            ];
            $rules['username'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($userId, 'user_id'),
            ];
            $rules['password'] = 'nullable|string|min:8';

            // Files are optional on update
            $rules['valid_id_photo'] = 'nullable|image|max:5120';
            $rules['valid_id_back_photo'] = 'nullable|image|max:5120';
            $rules['police_clearance'] = 'nullable|image|max:5120';

        } else {
            // Create Rules
            $rules['email'] = 'required|email|regex:/(.+)@(.+)\.(.+)/i|max:100|unique:users,email|unique:admin_users,email';

            // Files are required on create
            $rules['valid_id_photo'] = 'required|image|max:5120';
            $rules['valid_id_back_photo'] = 'required|image|max:5120';
            $rules['police_clearance'] = 'required|image|max:5120';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'date_of_birth.before' => 'The user is not eligible',
            'phone_number.regex' => 'Cellphone number should be in Philippine format, 11 digits',
            'email.regex' => 'Email should end in a proper email ending',
        ];
    }
}
