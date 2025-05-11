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
        'position'
    ];

    protected $casts = [
        'status' => 'string',
        'position' => 'integer'
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->orderBy('name');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}