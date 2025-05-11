<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'group_name'
    ];

    protected $casts = [
        'status' => 'string',
        'group_name' => 'string'
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}