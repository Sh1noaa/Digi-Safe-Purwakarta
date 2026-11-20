<?php

namespace App\Http\Controllers;

use App\Services\DailyStreakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyStreakController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'streak' => DailyStreakService::getStatusForUser(Auth::user()),
        ]);
    }

    public function answer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'option' => ['required', 'string', 'in:A,B,C,D,E,a,b,c,d,e'],
        ]);

        $result = DailyStreakService::submitAnswer(Auth::user(), (string) $validated['option']);
        $statusCode = (int) ($result['status'] ?? 200);
        unset($result['status']);

        return response()->json($result, $statusCode);
    }
}
