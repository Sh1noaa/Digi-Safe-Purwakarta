<?php

use App\Http\Controllers\Admin\EdukasiController as AdminEdukasiController;
use App\Http\Controllers\Admin\ForumController as AdminForumController;
use App\Http\Controllers\Admin\MinigameController as AdminMinigameController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\EdukasiController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MinigameController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialChatController;
use App\Http\Controllers\TentangController;
use App\Http\Controllers\TestimoniController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BerandaController::class, 'index'])->name('beranda');
Route::get('/download-apk', function () {
    $apkPath = public_path('downloads/digi-safe-purwakarta.apk');
    if (is_file($apkPath)) {
        return response()->download($apkPath, 'Digi-Safe-Purwakarta.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }

    return redirect()->away('https://www.mediafire.com/file/267zvidpwo13p4n/digi-safe-purwakarta.apk/file');
})->name('apk.download');
Route::get('/pendaftar', [BerandaController::class, 'usersDirectory'])->name('users.directory');
Route::get('/edukasi', [EdukasiController::class, 'index'])->name('edukasi');
Route::get('/tentang', [TentangController::class, 'index'])->name('tentang.index');

// rute halaman profil pengguna (lihat, edit profil, & pengaturan bahasa/tema/privasi)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings');
});
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

// halaman hub minigame, leaderboard & password checker publik, sedangkan arena kuis wajib login
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
Route::get('/minigame', [MinigameController::class, 'index'])->name('minigame.index');
Route::get('/minigame/password-checker', [MinigameController::class, 'passwordChecker'])->name('minigame.password-checker');

Route::middleware('auth')->group(function () {
    Route::post('/minigame/save-score', [MinigameController::class, 'saveScore'])->name('minigame.save-score');
    Route::get('/minigame/{type}', [MinigameController::class, 'play'])->name('minigame.play');

    // rute pertemanan & live chat antar pengguna
    Route::get('/chat/unread-count', [SocialChatController::class, 'unreadCount'])->name('chat.unread');
    Route::get('/chat/summary', [SocialChatController::class, 'summary'])->name('chat.summary');
    Route::get('/chat/users', [SocialChatController::class, 'searchUsers'])->name('chat.users');
    Route::get('/chat/messages/{user}', [SocialChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/messages/{user}', [SocialChatController::class, 'sendMessage'])->name('chat.send');

    Route::post('/friends/request/{user}', [SocialChatController::class, 'sendFriendRequest'])->name('friends.request');
    Route::post('/friends/accept/{user}', [SocialChatController::class, 'acceptFriendRequest'])->name('friends.accept');
    Route::post('/friends/decline/{user}', [SocialChatController::class, 'declineFriendRequest'])->name('friends.decline');
    Route::delete('/friends/{user}', [SocialChatController::class, 'removeFriendship'])->name('friends.remove');
});

// rute halaman forum komunitas & simpan jawaban + voting realtime ajax
Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/live-data', [ForumController::class, 'liveData'])->name('forum.live-data');
Route::post('/forum', [ForumController::class, 'store'])->middleware('auth')->name('forum.store');
Route::post('/forum/{forum}/jawaban', [ForumController::class, 'storeJawaban'])->middleware('auth')->name('forum.jawaban.store');
Route::post('/forum/jawaban/{jawaban}/vote', [ForumController::class, 'voteJawaban'])->middleware('auth')->name('forum.jawaban.vote');

// rute halaman testimoni & simpan ulasan
Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni.index');
Route::post('/testimoni', [TestimoniController::class, 'store'])->name('testimoni.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

// rute OAuth Google di luar guest middleware agar mode=mobile (APK) selalu lancar
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// rute panel admin (hanya untuk role admin dan owner)
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminEdukasiController::class, 'index'])->name('dashboard');

        // tab 1: manajemen edukasi
        Route::get('/edukasi', [AdminEdukasiController::class, 'index'])->name('edukasi.index');
        Route::post('/edukasi', [AdminEdukasiController::class, 'store'])->name('edukasi.store');
        Route::put('/edukasi/{edukasi}', [AdminEdukasiController::class, 'update'])->name('edukasi.update');
        Route::delete('/edukasi/{edukasi}', [AdminEdukasiController::class, 'destroy'])->name('edukasi.destroy');

        // tab 2: soal & level minigame
        Route::get('/minigame', [AdminMinigameController::class, 'index'])->name('minigame.index');
        Route::post('/minigame', [AdminMinigameController::class, 'store'])->name('minigame.store');
        Route::put('/minigame/{minigame}', [AdminMinigameController::class, 'update'])->name('minigame.update');
        Route::delete('/minigame/{minigame}', [AdminMinigameController::class, 'destroy'])->name('minigame.destroy');

        // kelola level minigame oleh admin
        Route::post('/minigame/levels', [AdminMinigameController::class, 'storeLevel'])->name('minigame.level.store');
        Route::put('/minigame/levels/{level}', [AdminMinigameController::class, 'updateLevel'])->name('minigame.level.update');
        Route::delete('/minigame/levels/{level}', [AdminMinigameController::class, 'destroyLevel'])->name('minigame.level.destroy');

        // tab 3: moderasi forum
        Route::get('/forum', [AdminForumController::class, 'index'])->name('forum.index');
        Route::patch('/forum/{forum}/star', [AdminForumController::class, 'toggleStar'])->name('forum.star');
        Route::delete('/forum/{forum}', [AdminForumController::class, 'destroy'])->name('forum.destroy');

        // tab 4: hak akses user
        Route::get('/users', [AdminUserController::class, 'index'])->name('user.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('user.role');

        // download laporan excel (.xlsx)
        Route::get('/report/xlsx', [AdminReportController::class, 'downloadXlsx'])->name('report.xlsx');
    });

// fallback media storage publik untuk shared hosting (RumahWeb) tanpa SSH storage:link
Route::get('/storage/{path}', function (string $path) {
    $baseDir = realpath(storage_path('app/public'));
    $fullPath = $baseDir ? realpath($baseDir . DIRECTORY_SEPARATOR . ltrim($path, '/\\')) : false;

    if (! $baseDir || ! $fullPath || ! str_starts_with($fullPath, $baseDir) || ! is_file($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*')->name('storage.fallback');
