<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundSettingsTimeline extends Model
{
    protected $table = 'budget_settings_timeline';

    protected $fillable = [
        'fund_setting_id',
        'action',
        'user_id',
    ];

    // Relationship: Timeline belongs to a FundSetting
    public function fundSetting()
    {
        return $this->belongsTo(FundSettings::class, 'budget_setting_id');
    }

    // (Optional) Relationship: Timeline belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}