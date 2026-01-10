<?php

namespace App\Http\Requests\Both;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Sanctum\PersonalAccessToken;

class disputeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $action = $this->route()->getActionMethod();

        switch ($action) {
            case 'fileDispute':
                return $this->fileDisputeRules();
            default:
                return [];
        }
    }

    protected function fileDisputeRules()
    {
        $rules = [
            'project_id' => [
                'required',
                'integer',
                'exists:projects,project_id',
                function ($attribute, $value, $fail) {
                    $project = \DB::table('projects as p')
                        ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                        ->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
                        ->where('p.project_id', $value)
                        ->select('pr.owner_id', 'c.user_id as contractor_user_id')
                        ->first();

                    if (!$project) {
                        $fail('The selected project does not exist.');
                        return;
                    }

                    if (!$project->owner_id) {
                        $fail('Project owner not found.');
                        return;
                    }

                    $ownerExists = \DB::table('property_owners')->where('owner_id', $project->owner_id)->exists() ||
                                   \DB::table('users')->where('user_id', $project->owner_id)->exists();

                    if (!$ownerExists) {
                        $fail('Project owner not found.');
                        return;
                    }

                    if ($project->contractor_user_id && !\DB::table('users')->where('user_id', $project->contractor_user_id)->exists()) {
                        $fail('Project contractor user not found.');
                        return;
                    }
                }
            ],
            'milestone_id' => [
                'required',
                'integer',
                'exists:milestones,milestone_id'
            ],
            'milestone_item_id' => [
                'required',
                'integer',
                'exists:milestone_items,item_id',
                function ($attribute, $value, $fail) {
                    try {
                        if ($value) {
                            // Try to get user from session first
                            $user = \Session::get('user');
                            $userId = null;
                            
                            // If no session user, try to get from request (for API token auth)
                            if (!$user) {
                                $request = request();
                                $bearerToken = $request->bearerToken();
                                if ($bearerToken) {
                                    try {
                                        $token = PersonalAccessToken::findToken($bearerToken);
                                        if ($token) {
                                            $user = $token->tokenable;
                                        }
                                    } catch (\Exception $e) {
                                        // Token lookup failed, continue without user
                                        \Log::warning('disputeRequest validation: Token lookup failed', ['error' => $e->getMessage()]);
                                    }
                                }
                                
                                // Fallback to request->user() if available
                                if (!$user && $request->user()) {
                                    $user = $request->user();
                                }
                            }
                            
                            if ($user && isset($user->user_id)) {
                                $userId = $user->user_id;
                                if ($userId) {
                                    $existingDispute = \DB::table('disputes')
                                        ->where('milestone_item_id', $value)
                                        ->where('raised_by_user_id', $userId)
                                        ->whereIn('dispute_status', ['open', 'under_review'])
                                        ->first();

                                    if ($existingDispute) {
                                        $fail('You already have an open dispute for this milestone item. Please wait for it to be resolved or closed before filing another dispute.');
                                    }
                                }
                            }
                            // If no user found, skip this validation check (will be handled in controller)
                        }
                    } catch (\Exception $e) {
                        // Log validation error but don't fail validation - let controller handle auth
                        \Log::warning('disputeRequest validation error in milestone_item_id check', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Don't fail validation - controller will handle authentication
                    }
                }
            ],
            'dispute_type' => 'required|string|in:Payment,Delay,Quality,Others',
            'dispute_desc' => 'required|string|max:2000',
            'if_others_distype' => 'nullable|required_if:dispute_type,Others|string|max:255',
            'evidence_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
                'max:5120'
            ],
            'evidence_files' => 'nullable|array|max:10',
            'evidence_files.*' => [
                'file',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
                'max:5120'
            ]
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'project_id.required' => 'Please select a project.',
            'project_id.integer' => 'Invalid project selected.',
            'project_id.exists' => 'The selected project does not exist.',

            'milestone_id.integer' => 'Invalid milestone selected.',
            'milestone_id.exists' => 'The selected milestone does not exist.',

            'milestone_item_id.required' => 'Please select a milestone item.',
            'milestone_item_id.integer' => 'Invalid milestone item selected.',
            'milestone_item_id.exists' => 'The selected milestone item does not exist.',

            'dispute_type.required' => 'Please select a dispute type.',
            'dispute_type.in' => 'Invalid dispute type selected. Must be Payment, Delay, Quality, or Others.',

            'dispute_desc.required' => 'Please provide a detailed description of the dispute.',
            'dispute_desc.max' => 'Dispute description cannot exceed 2000 characters.',

            'if_others_distype.required_if' => 'Please specify the dispute type when "Others" is selected.',
            'if_others_distype.max' => 'The specified dispute type cannot exceed 255 characters.',

            'evidence_file.file' => 'Evidence must be a valid file.',
            'evidence_file.mimes' => 'Evidence file must be JPG, JPEG, PNG, PDF, DOC, or DOCX format.',
            'evidence_file.max' => 'Evidence file must not exceed 5MB.',

            'evidence_files.array' => 'Evidence files must be an array.',
            'evidence_files.max' => 'You can upload a maximum of 10 evidence files.',
            'evidence_files.*.file' => 'Each evidence file must be a valid file.',
            'evidence_files.*.mimes' => 'Each evidence file must be JPG, JPEG, PNG, PDF, DOC, or DOCX format.',
            'evidence_files.*.max' => 'Each evidence file must not exceed 5MB.'
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

