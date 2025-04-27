<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestTimeline extends Model
{
    protected $table = 'request_timeline';

    protected $fillable = [
        'request_id',
        'approver_id',
        'approved_date',
        'approved_progress',
        'approved_status',
        'remarks'
    ];

    protected $casts = [
        'approved_date' => 'datetime'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}