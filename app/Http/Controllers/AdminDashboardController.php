<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $users = User::select(['id', 'name', 'role', 'status'])->get();

        return Inertia::render('AdminDashboard', [
            'activeUsers' => $users
        ]);
    }
}