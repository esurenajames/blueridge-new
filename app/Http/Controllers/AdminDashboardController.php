<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')    
            ->get();

        $userStats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        $roleDistribution = User::all()
            ->groupBy('role')
            ->map(fn ($group) => $group->count())
            ->toArray();

        return Inertia::render('AdminDashboard', [
            'activeUsers' => $users,
            'userStats' => [
                ...$userStats,
                'roles' => $roleDistribution,
            ],
        ]);
    }
}