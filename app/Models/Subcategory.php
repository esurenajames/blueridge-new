<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
        'category_id' => 'integer'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function budget()
    {
        return $this->hasOne(Budget::class);
    }
}