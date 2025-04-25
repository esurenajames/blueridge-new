<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CaptainController extends Controller
{
    function index()
    {
        return Inertia::render("CaptainDashboard");
    }
}
