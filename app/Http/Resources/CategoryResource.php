<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'group_name' => $this->group_name,
            'position' => $this->position,
            'status' => $this->status,
            'subcategories_count' => $this->whenCounted('subcategories'),
            'subcategories' => $this->whenLoaded('subcategories', function() {
                return $this->subcategories->map(function($subcategory) {
                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'description' => $subcategory->description,
                        'status' => $subcategory->status
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}