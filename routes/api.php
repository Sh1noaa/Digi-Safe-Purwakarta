<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\MinigameApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\PublicContentApiController;
use App\Http\Controllers\SocialChatController;
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
    Route::get('/leaderboard', [PublicContentApiController::class, 'leaderboard']);

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
    Route::put('/profile/role', [ProfileApiController::class, 'updateRole']);
    Route::put('/profile/settings', [ProfileApiController::class, 'updateSettings']);

    Route::get('/edukasi/requests', [PublicContentApiController::class, 'myEdukasiRequests']);
    Route::post('/edukasi/requests', [PublicContentApiController::class, 'storeEdukasiRequest']);
    Route::post('/edukasi/requests/mark-seen', [PublicContentApiController::class, 'markEdukasiRequestsSeen']);

    Route::get('/minigame/{type}', [MinigameApiController::class, 'questions']);
    Route::post('/minigame/score', [MinigameApiController::class, 'saveScore']);

    Route::post('/forum', [ForumApiController::class, 'store']);
    Route::post('/forum/{forum}/jawaban', [ForumApiController::class, 'storeJawaban']);
    Route::post('/forum/jawaban/{jawaban}/vote', [ForumApiController::class, 'voteJawaban']);

    // Sosial Chat, Pertemanan & Bot Digika (langsung menggunakan SocialChatController via Bearer Token)
    Route::get('/chat/unread-count', [SocialChatController::class, 'unreadCount']);
    Route::get('/chat/summary', [SocialChatController::class, 'summary']);
    Route::get('/chat/users', [SocialChatController::class, 'searchUsers']);
    Route::get('/chat/messages/{user}', [SocialChatController::class, 'messages']);
    Route::post('/chat/messages/{user}', [SocialChatController::class, 'sendMessage']);
    Route::post('/friends/request/{user}', [SocialChatController::class, 'sendFriendRequest']);
    Route::post('/friends/accept/{user}', [SocialChatController::class, 'acceptFriendRequest']);
    Route::post('/friends/decline/{user}', [SocialChatController::class, 'declineFriendRequest']);
    Route::delete('/friends/{user}', [SocialChatController::class, 'removeFriendship']);
});

// Endpoint API khusus Admin & Owner
Route::middleware(['api.auth', 'api.admin'])->prefix('admin')->group(function () {
    Route::get('/summary', [AdminApiController::class, 'summary']);
    Route::get('/overview', [AdminApiController::class, 'overview']);
    Route::post('/edukasi', [AdminApiController::class, 'storeEdukasi']);
    Route::put('/edukasi/{edukasi}', [AdminApiController::class, 'updateEdukasi']);
    Route::delete('/edukasi/{edukasi}', [AdminApiController::class, 'deleteEdukasi']);
    Route::patch('/edukasi/requests/{edukasiRequest}/approve', [AdminApiController::class, 'approveEdukasiRequest']);
    Route::patch('/edukasi/requests/{edukasiRequest}/reject', [AdminApiController::class, 'rejectEdukasiRequest']);

    Route::post('/minigame', [AdminApiController::class, 'storeMinigameQuestion']);
    Route::delete('/minigame/{minigame}', [AdminApiController::class, 'deleteMinigameQuestion']);
    Route::post('/minigame/levels', [AdminApiController::class, 'storeMinigameLevel']);
    Route::delete('/minigame/levels/{level}', [AdminApiController::class, 'deleteMinigameLevel']);

    Route::patch('/forum/{forum}/star', [AdminApiController::class, 'toggleForumStar']);
    Route::delete('/forum/{forum}', [AdminApiController::class, 'deleteForum']);
    Route::patch('/users/{user}/role', [AdminApiController::class, 'updateUserRole']);
});
