<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\MinigameApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\PublicContentApiController;
use Illuminate\Support\Facades\Route;

// Endpoint API publik & opsional auth (otomatis membaca Bearer token & X-App-Locale)
Route::middleware('api.optional')->group(function () {
    Route::get('/health', fn () => response()->json(['ok' => true, 'app' => 'Digi-Safe Purwakarta API']));

    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::post('/auth/register', [AuthApiController::class, 'register']);
    Route::post('/auth/google', [AuthApiController::class, 'googleTokenLogin']);

    Route::get('/beranda', [PublicContentApiController::class, 'beranda']);
    Route::get('/users', [PublicContentApiController::class, 'users']);
    Route::get('/users/{identifier}', [PublicContentApiController::class, 'userProfile']);
    Route::get('/edukasi', [PublicContentApiController::class, 'edukasi']);
    Route::get('/tentang', [PublicContentApiController::class, 'tentang']);

    Route::get('/testimoni', [PublicContentApiController::class, 'testimoni']);
    Route::post('/testimoni', [PublicContentApiController::class, 'storeTestimoni']);

    Route::get('/minigame', [MinigameApiController::class, 'index']);
    Route::get('/forum', [ForumApiController::class, 'index']);
});

// Endpoint API yang wajib login (Bearer Token)
Route::middleware('api.auth')->group(function () {
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::post('/profile', [ProfileApiController::class, 'update']);
    Route::put('/profile/settings', [ProfileApiController::class, 'updateSettings']);

    Route::get('/minigame/{type}', [MinigameApiController::class, 'questions']);
    Route::post('/minigame/score', [MinigameApiController::class, 'saveScore']);

    Route::post('/forum', [ForumApiController::class, 'store']);
    Route::post('/forum/{forum}/jawaban', [ForumApiController::class, 'storeJawaban']);
    Route::post('/forum/jawaban/{jawaban}/vote', [ForumApiController::class, 'voteJawaban']);
});

// Endpoint API khusus Admin & Owner
Route::middleware(['api.auth', 'api.admin'])->prefix('admin')->group(function () {
    Route::get('/summary', [AdminApiController::class, 'summary']);
    Route::post('/edukasi', [AdminApiController::class, 'storeEdukasi']);
    Route::delete('/edukasi/{edukasi}', [AdminApiController::class, 'deleteEdukasi']);
    Route::patch('/forum/{forum}/star', [AdminApiController::class, 'toggleForumStar']);
});

