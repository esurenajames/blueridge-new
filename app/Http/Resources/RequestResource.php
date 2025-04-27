<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $progress = 0;
        if ($this->status !== 'pending' && $this->status !== 'voided') {
            $progress += 25;
            if ($this->quotation()->exists()) $progress += 25;
            if ($this->purchaseRequest()->exists()) $progress += 25;
            if ($this->purchaseOrder()->exists()) $progress += 25;
        }

        return [
            'id' => $this->id,
            'title' => $this->name,
            'category' => $this->category,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at->format('Y-m-d'),
            'created_by' => $this->creator ? $this->creator->name : 'Unknown User',
            'progress' => $progress,
            'stages' => [
                'form' => $this->status !== 'pending',
                'quotation' => $this->quotation()->exists(),
                'purchaseRequest' => $this->purchaseRequest()->exists(),
                'purchaseOrder' => $this->purchaseOrder()->exists(),
            ],
            'collaborators' => $this->collaborators->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'permission' => $c->pivot->permission
            ]),
            'files' => $this->files->map(fn($f) => [
                'name' => $f->name,
                'size' => $f->size,
                'uploaded_at' => $f->created_at->format('Y-m-d'),
            ]),
        ];
    }
}