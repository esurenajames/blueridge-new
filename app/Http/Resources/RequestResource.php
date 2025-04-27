<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progress = 0;
        
        $approvedStages = $this->timelines
            ->where('approved_status', 'approved')
            ->pluck('approved_progress')
            ->toArray();
        
        if (in_array('Request Form', $approvedStages)) $progress += 25;
        if (in_array('Quotation', $approvedStages)) $progress += 25;
        if (in_array('Purchase Request', $approvedStages)) $progress += 25;
        if (in_array('Purchase Order', $approvedStages)) $progress += 25;

        return [
            'id' => $this->id,
            'title' => $this->name,
            'category' => $this->category,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at->format('Y-m-d'),
            'created_by' => $this->creator ? $this->creator->name : 'Unknown User',
            'processed_by' => $this->processor ? $this->processor->name : null,
            'processed_at' => $this->processed_at ? Carbon::parse($this->processed_at)->format('Y-m-d') : null,
            'progress' => $progress,
            'stages' => [
                'form' => in_array('Request Form', $approvedStages),
                'quotation' => in_array('Quotation', $approvedStages),
                'purchaseRequest' => in_array('Purchase Request', $approvedStages),
                'purchaseOrder' => in_array('Purchase Order', $approvedStages),
            ],
            'collaborators' => $this->whenLoaded('collaborators', fn() => $this->collaborators->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'role' => $c->role,
                'permission' => $c->pivot->permission
            ])),
            'files' => $this->whenLoaded('files', fn() => $this->files->map(fn($f) => [
                'name' => $f->name,
                'size' => $f->size,
                'uploaded_at' => $f->created_at->format('Y-m-d'),
            ])),
            'timelines' => $this->whenLoaded('timelines', fn() => $this->timelines
                ->sortBy('approved_date')  // Changed from sortByDesc to sortBy
                ->values()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'approver_name' => $t->approver->name,
                    'approved_date' => Carbon::parse($t->approved_date)->format('Y-m-d H:i:s'),
                    'approved_progress' => $t->approved_progress,
                    'approved_status' => $t->approved_status,
                    'remarks' => $t->remarks
                ])
            ),
        ];
    }
}