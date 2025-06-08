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
            'quotation' => $this->when($this->relationLoaded('quotation'), function() {
                return [
                    'id' => $this->quotation?->id,
                    'status' => $this->quotation?->status,
                    'have_quotation' => $this->quotation?->have_quotation,
                    'processed_by' => $this->quotation?->processor?->name,
                    'processed_at' => $this->quotation?->processed_at?->format('Y-m-d'),
                    'details' => $this->when($this->quotation?->relationLoaded('details'), function() {
                        return $this->quotation->details->map(function($detail) {
                            return [
                                'id' => $detail->id,
                                'is_selected' => $detail->is_selected,
                                'company' => $this->when($detail->relationLoaded('company'), function() use ($detail) {
                                    return [
                                        'id' => $detail->company->id,
                                        'company_name' => $detail->company->company_name,
                                        'contact_person' => $detail->company->contact_person,
                                        'address' => $detail->company->address,
                                        'contact_number' => $detail->company->contact_number,
                                        'email' => $detail->company->email,
                                    ];
                                }),
                                'items' => $this->when($detail->relationLoaded('items'), function() use ($detail) {
                                    return $detail->items->map(function($item) {
                                        return [
                                            'id' => $item->id,
                                            'item_name' => $item->item_name,
                                            'description' => $item->description,
                                            'price' => $item->price,
                                            'quantity' => $item->quantity,
                                            'total' => $item->price * $item->quantity,
                                        ];
                                    });
                                }),
                            ];
                        });
                    }),
                ];
            }),
            'purchaseRequest' => $this->when($this->relationLoaded('purchaseRequest'), function() {
                return [
                    'id' => $this->purchaseRequest?->id,
                    'status' => $this->purchaseRequest?->status,
                    'have_supplier_approval' => (bool) $this->purchaseRequest?->have_supplier_approval,
                    'processed_by' => $this->purchaseRequest?->processor?->name,
                    'processed_at' => $this->purchaseRequest?->processed_at?->format('Y-m-d'),
                ];
            }),
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
                ->sortBy(function($timeline) {
                    // Use the earliest date between processed_date and approved_date
                    if ($timeline->processed_date && $timeline->approved_date) {
                        return min($timeline->processed_date, $timeline->approved_date);
                    }
                    return $timeline->processed_date ?? $timeline->approved_date;
                })
                ->values()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'approver_name' => $t->approver?->name ?? 'N/A',
                    'processor_name' => $t->processor?->name ?? 'N/A',
                    'approved_date' => $t->approved_date ? Carbon::parse($t->approved_date)->format('Y-m-d') : null,
                    'processed_date' => $t->processed_date ? Carbon::parse($t->processed_date)->format('Y-m-d') : null,
                    'approved_progress' => $t->approved_progress ?? null,
                    'processed_progress' => $t->processed_progress ?? null,
                    'approved_status' => $t->approved_status ?? null,
                    'processed_status' => $t->processed_status ?? null,
                    'remarks' => $t->remarks ?? null
                ])
            ),
        ];
    }
}
