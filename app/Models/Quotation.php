<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $table = 'request_quotations';
    protected $fillable = [
        'request_id',
        'status',
        'have_quotation',
        'processed_by',
        'processed_at'
    ];

    protected $casts = [
        'processed_at' => 'datetime'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(QuotationDetail::class, 'request_quotation_id');
    }
}