<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $users = User::select(['id', 'name', 'role', 'status'])
            ->where('status', 'active')
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