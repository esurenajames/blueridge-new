<?php

namespace App\Http\Controllers\Captain;

use App\Http\Resources\FundSettingsResource;
use App\Http\Requests\FundSettingsRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FundSettings;
use App\Models\FundSettingsTimeline;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CaptainFundSettingsController extends Controller
{
    // Get all fund settings
    public function index()
    {
        $settings = FundSettingsResource::collection(FundSettings::all());
        return Inertia::render('captain/CaptainFundSettings', [
            'settings' => $settings,
        ]);
    }

    public function saveChanges(FundSettingsRequest $request)
{
    $userId = Auth::id();
    $timeline = [];

    foreach ($request->input('settings') as $item) {
        $setting = FundSettings::find($item['id']);

        if ($setting && $setting->is_locked !== $item['is_locked']) {
            $setting->is_locked = $item['is_locked'];
            $setting->save();

            $timeline[] = [
                'budget_setting_id' => $setting->id,
                'action' => $item['is_locked'] ? 'locked' : 'unlocked',
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    if (!empty($timeline)) {
        FundSettingsTimeline::insert($timeline);
    }

    $settings = FundSettingsResource::collection(FundSettings::all());

    return redirect()->back()->with([
        'success' => 'Settings updated successfully',
        'settings' => $settings
    ]);
}
}