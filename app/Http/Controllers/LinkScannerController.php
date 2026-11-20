<?php

namespace App\Http\Controllers;

use App\Services\PhishingLinkScannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkScannerController extends Controller
{
    public function show(): View
    {
        return view('minigame.link-scanner');
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:1000'],
        ]);

        $isEn = app()->getLocale() === 'en';
        $result = PhishingLinkScannerService::analyze((string) $validated['url'], $isEn);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
