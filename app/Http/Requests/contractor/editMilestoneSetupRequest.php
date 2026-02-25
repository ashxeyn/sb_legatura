<?php

namespace App\Http\Requests\contractor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Session;

class editMilestoneSetupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'items' => 'required|json'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateMilestoneItems($validator);
        });
    }

    protected function validateMilestoneItems($validator)
    {
        $step1 = Session::get('milestone_setup_step1');
        $step2 = Session::get('milestone_setup_step2');

        if (!$step1 || !$step2) {
            $validator->errors()->add('session', 'Session expired. Please start again.');
            return;
        }

        $itemsRaw = $this->input('items');
        $items = json_decode($itemsRaw, true);

        if (!is_array($items) || empty($items)) {
            $validator->errors()->add('items', 'Please add at least one milestone item');
            return;
        }

        $totalPercentage = 0;
        $startDate = strtotime($step2['start_date']);
        $endDate = strtotime($step2['end_date']);

        foreach ($items as $index => $item) {
            if (!isset($item['percentage'], $item['title'], $item['description'], $item['date_to_finish'])) {
                $validator->errors()->add('items', 'Each milestone item must have percentage, title, description, and date');
                return;
            }

            $percentage = (float) $item['percentage'];
            if ($percentage <= 0) {
                $validator->errors()->add('items', 'Percentage must be greater than zero');
                return;
            }

            $itemDate = strtotime($item['date_to_finish']);
            if ($itemDate === false) {
                $validator->errors()->add('items', 'Invalid date format for milestone item');
                return;
            }

            if ($itemDate < $startDate) {
                $validator->errors()->add('items', 'Milestone item date cannot be before the project start date');
                return;
            }

            if ($itemDate > $endDate) {
                $validator->errors()->add('items', 'Milestone item date cannot be after the project end date');
                return;
            }

            $totalPercentage += $percentage;
        }

        if (round($totalPercentage, 2) !== 100.00) {
            $validator->errors()->add('items', 'Milestone percentages must add up to exactly 100%');
            return;
        }

        $lastItem = end($items);
        $lastDate = strtotime($lastItem['date_to_finish']);
        if (date('Y-m-d', $lastDate) !== date('Y-m-d', $endDate)) {
            $validator->errors()->add('items', 'The last milestone item must finish on the project end date');
            return;
        }
    }

    public function messages()
    {
        return [
            'items.required' => 'Please provide at least one milestone item',
            'items.json' => 'Invalid milestone items data'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422)
        );
    }
}
