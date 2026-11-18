<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(): View
    {
        $leaderboardData = LeaderboardService::allCategories(10);

        return view('leaderboard', compact('leaderboardData'));
    }
}

