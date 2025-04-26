<?php

namespace App\Http\Controllers\Captain;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class CaptainDashboardController extends Controller
{
    function index()
    {
        return Inertia::render("captain/CaptainDashboard");
    }
}
