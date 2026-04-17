<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $approvedStages = $this->timelines
            ->where('approved_status', 'approved')
            ->pluck('approved_progress')
            ->toArray();

        return [
            'id' => $this->id,
            'title' => $this->name,
            'category_id' => $this->category_id,
            'category' => $this->category->name,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at->format('Y-m-d'),
            'created_by' => $this->creator ? $this->creator->name : 'Unknown User',
            'processed_by' => $this->processor ? $this->processor->name : null,
            'processed_at' => $this->processed_at ? Carbon::parse($this->processed_at)->format('Y-m-d') : null,
            'progress' => $this->progress, 
            'is_completed' => $this->is_completed,
            'stages' => [
                'form' => in_array('Request Form', $approvedStages),
                'quotation' => in_array('Quotation', $approvedStages),
                'purchaseRequest' => in_array('Purchase Request', $approvedStages),
                'purchaseOrder' => in_array('Purchase Order', $approvedStages),
            ],
            'quotation' => $this->whenLoaded('quotation'),
            'purchaseRequest' => $this->whenLoaded('purchaseRequest'),
            'collaborators' => $this->whenLoaded('collaborators'),
            'files' => $this->whenLoaded('files'),
            'timelines' => $this->whenLoaded('timelines'),
        ];
    }
}
