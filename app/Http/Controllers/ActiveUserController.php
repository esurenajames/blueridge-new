<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActiveUserController extends Controller
{
    public function index()
    {
        $activeUsers = User::where('last_active_at', '>=', Carbon::now()->subWeek())
            ->orderBy('last_active_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'last_active_at' => Carbon::parse($user->last_active_at)->diffForHumans(),
                    'actions_count' => $user->actions_count ?? 0
                ];
            });

        return inertia('Admin/Dashboard', [
            'activeUsers' => $activeUsers
        ]);
    }
}