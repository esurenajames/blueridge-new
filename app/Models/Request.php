<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Request extends Model
{
    protected $table = 'requests';
    protected $fillable = [
        'name',
        'category',
        'description',
        'progress',
        'status',
        'created_by',
        'processed_by',
        'processed_at'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class);
    }

    public function purchaseRequest(): HasOne
    {
        return $this->hasOne(PurchaseRequest::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(RequestFile::class);
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'request_collaborators')
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function processor()
    {
    return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(RequestTimeline::class)
            ->orderByRaw('COALESCE(processed_date, approved_date) DESC');
    }
}