<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_name' => $this->request ? $this->request->name : null, 
            'subcategory_name' => $this->budget->subcategory->name ?? 'Unknown',
            'transaction_date' => $this->transaction_date,
            'type' => $this->type,
            'total_amount' => $this->total_amount,
            'remarks' => $this->remarks,
            'processed_by' => $this->processedBy->name ?? 'Unknown',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'files' => $this->files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'path' => $file->path,
                    'size' => $file->size,
                    'file_type' => $file->file_type,
                    'url' => $file->path ? asset('storage/' . $file->path) : null,
                ];
            }),
        ];
    }
}