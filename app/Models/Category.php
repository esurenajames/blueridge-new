<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'position',
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

    public function scopeByGroup($query, string $groupName)
    {
        return $query->where('group_name', $groupName)->orderBy('position');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (!$category->position) {
                $lastPosition = static::where('group_name', $category->group_name)
                    ->max('position');
                $category->position = ($lastPosition ?? 0) + 1;
            }
        });
    }
}