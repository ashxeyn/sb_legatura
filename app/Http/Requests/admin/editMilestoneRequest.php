<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class editMilestoneRequest extends FormRequest
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
            'milestone_item_title' => 'required|string|max:200',
            'milestone_item_description' => 'required|string',
            'date_to_finish' => 'required|date',
            'milestone_item_cost' => 'required|numeric|min:0',
            'item_status' => 'required|in:pending,not_started,in_progress,delayed,completed,cancelled,halt,deleted'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $itemId = $this->route('itemId');
            $newDate = $this->date_to_finish;

            // Get the current milestone item
            $currentItem = DB::table('milestone_items')
                ->where('item_id', $itemId)
                ->first();

            if (!$currentItem) {
                $validator->errors()->add('date_to_finish', 'Milestone item not found');
                return;
            }

            // Get the project_id through milestone
            $milestone = DB::table('milestones')
                ->where('milestone_id', $currentItem->milestone_id)
                ->first();

            if (!$milestone) {
                $validator->errors()->add('date_to_finish', 'Milestone not found');
                return;
            }

            $projectId = $milestone->project_id;

            // Get all milestone items in the same project (excluding current item)
            $allItems = DB::table('milestone_items')
                ->join('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
                ->where('milestones.project_id', $projectId)
                ->where('milestone_items.item_id', '!=', $itemId)
                ->select('milestone_items.*')
                ->orderBy('milestone_items.sequence_order', 'asc')
                ->get();

            // Check for date conflicts
            $newDateTime = strtotime($newDate);
            
            foreach ($allItems as $item) {
                $itemDateTime = strtotime($item->date_to_finish);
                
                // Check if dates are exactly the same
                if (date('Y-m-d', $newDateTime) === date('Y-m-d', $itemDateTime)) {
                    $validator->errors()->add(
                        'date_to_finish',
                        'This date conflicts with another milestone item: "' . $item->milestone_item_title . '". Each milestone must have a unique completion date.'
                    );
                    return;
                }
            }

            // Check sequence order logic: dates should be in ascending order
            $currentSequence = $currentItem->sequence_order;
            
            foreach ($allItems as $item) {
                $itemDateTime = strtotime($item->date_to_finish);
                
                // If this item comes before the current item in sequence, its date should be earlier
                if ($item->sequence_order < $currentSequence && $itemDateTime >= $newDateTime) {
                    $validator->errors()->add(
                        'date_to_finish',
                        'This date must be after milestone "' . $item->milestone_item_title . '" (Sequence ' . $item->sequence_order . ') which ends on ' . date('M d, Y', $itemDateTime) . '.'
                    );
                    return;
                }
                
                // If this item comes after the current item in sequence, its date should be later
                if ($item->sequence_order > $currentSequence && $itemDateTime <= $newDateTime) {
                    $validator->errors()->add(
                        'date_to_finish',
                        'This date must be before milestone "' . $item->milestone_item_title . '" (Sequence ' . $item->sequence_order . ') which ends on ' . date('M d, Y', $itemDateTime) . '.'
                    );
                    return;
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'milestone_item_title.required' => 'item title is required',
            'milestone_item_title.max' => 'item title must not exceed 200 characters',
            'milestone_item_description.required' => 'item description is required',
            'date_to_finish.required' => 'date to finish is required',
            'date_to_finish.date' => 'date to finish must be a valid date',
            'milestone_item_cost.required' => 'estimated cost is required',
            'milestone_item_cost.numeric' => 'estimated cost must be a number',
            'milestone_item_cost.min' => 'estimated cost must be at least 0',
            'item_status.required' => 'item status is required',
            'item_status.in' => 'item status must be a valid status'
        ];
    }
}
