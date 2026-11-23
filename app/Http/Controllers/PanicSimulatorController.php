<?php

namespace App\Http\Controllers;

use App\Models\SkorGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PanicSimulatorController extends Controller
{
    public function show(): View
    {
        return view('minigame.panic-simulator');
    }

    public function recordResult(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scenario' => ['nullable', 'string', 'in:apk_breach,wa_hijack,phishing_bank'],
            'mode' => ['required', 'string', 'in:standard,extreme'],
            'won' => ['required', 'boolean'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'saved_balance' => ['required', 'integer', 'min:0', 'max:10000000'],
            'elapsed_seconds' => ['required', 'integer', 'min:0', 'max:90'],
            'steps_completed' => ['required', 'integer', 'min:0', 'max:5'],
            'mistakes' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $savedToDb = false;

        if (Auth::check() && Schema::hasTable('skor_game')) {
            $userId = (int) Auth::id();
            $points = (int) $validated['score'];

            $prevMax = (int) SkorGame::where('user_id', $userId)
                ->where('game_type', 'panic-simulator')
                ->max('skor_tertinggi');

            SkorGame::create([
                'user_id' => $userId,
                'game_type' => 'panic-simulator',
                'skor' => $points,
                'skor_tertinggi' => max($prevMax, $points),
                'benar' => (int) $validated['steps_completed'],
                'salah' => (int) $validated['mistakes'],
                'played_at' => now(),
            ]);

            $savedToDb = true;
        }

        return response()->json([
            'ok' => true,
            'saved' => $savedToDb,
        ]);
    }
}
