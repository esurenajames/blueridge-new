<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundSettings extends Model
{
    protected $table = 'budget_settings';

    protected $fillable = [
        'name',
        'is_locked',
    ];

    // Relationship: FundSettings has many timeline entries
    public function timelines()
    {
        return $this->hasMany(FundSettingsTimeline::class, 'budget_setting_id');
    }
}