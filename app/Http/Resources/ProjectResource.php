<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'project_id' => $this->project_id,
            'owner_id' => $this->owner_id,
            'project_title' => $this->project_title,
            'project_description' => $this->project_description,
            'project_location' => $this->project_location,
            'budget_range_min' => $this->budget_range_min,
            'budget_range_max' => $this->budget_range_max,
            'lot_size' => $this->lot_size,
            'property_type' => $this->property_type,
            'type_id' => $this->type_id,
            'to_finish' => $this->to_finish,
            'project_status' => $this->project_status,
            'selected_contractor_id' => $this->selected_contractor_id,
            'bidding_deadline' => $this->bidding_deadline,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'files' => DB::table('project_files')->where('project_id', $this->project_id)->get(),
        ];
    }
}


