<?php

namespace App\Http\Controllers;

use App\Models\SkorGame;
use App\Services\WaScamSimulatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WaSimulatorController extends Controller
{
    public function show(): View
    {
        $isEn = app()->getLocale() === 'en';
        $contacts = WaScamSimulatorService::contacts($isEn);

        return view('minigame.wa-simulator', [
            'contacts' => $contacts,
        ]);
    }

    public function recordResult(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => ['required', 'string', 'max:60'],
            'ending_type' => ['required', 'string', 'in:safe,partial,danger'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (Auth::check() && Schema::hasTable('skor_game')) {
            $userId = (int) Auth::id();
            $points = match ($validated['ending_type']) {
                'safe' => 30,
                'partial' => 15,
                default => 0,
            };
            $benar = match ($validated['ending_type']) {
                'safe', 'partial' => 1,
                default => 0,
            };
            $salah = match ($validated['ending_type']) {
                'danger', 'partial' => 1,
                default => 0,
            };

            $prevMax = (int) SkorGame::where('user_id', $userId)
                ->where('game_type', 'wa-simulator')
                ->max('skor_tertinggi');

            SkorGame::create([
                'user_id' => $userId,
                'game_type' => 'wa-simulator',
                'skor' => $points,
                'skor_tertinggi' => max($prevMax, $points),
                'benar' => $benar,
                'salah' => $salah,
                'played_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
