<?php

use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\Admin\EdukasiController as AdminEdukasiController;
use App\Http\Controllers\Admin\ForumController as AdminForumController;
use App\Http\Controllers\Admin\MinigameController as AdminMinigameController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StreakQuestionController as AdminStreakQuestionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DailyStreakController;
use App\Http\Controllers\EdukasiController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LinkScannerController;
use App\Http\Controllers\MinigameController;
use App\Http\Controllers\PanicSimulatorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialChatController;
use App\Http\Controllers\TentangController;
use App\Http\Controllers\TestimoniController;
use App\Http\Controllers\WaSimulatorController;
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
Route::post('/preferences/quick', [ProfileController::class, 'quickPreferences'])->middleware('throttle:30,1')->name('preferences.quick');

// rute halaman profil pengguna (lihat, edit profil, & pengaturan bahasa/tema/privasi)
Route::middleware('auth')->group(function () {
    Route::post('/edukasi/request', [EdukasiController::class, 'storeRequest'])->middleware('throttle:10,1')->name('edukasi.request.store');
    Route::post('/edukasi/request/mark-seen', [EdukasiController::class, 'markRequestsSeen'])->name('edukasi.request.seen');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:20,1')->name('profile.update');
    Route::put('/profile/role', [ProfileController::class, 'updateRole'])->middleware('throttle:20,1')->name('profile.role');
    Route::put('/profile/border', [ProfileController::class, 'updateBorder'])->middleware('throttle:20,1')->name('profile.border');
    Route::put('/profile/settings', [ProfileController::class, 'updateSettings'])->middleware('throttle:20,1')->name('profile.settings');
    Route::post('/profile/delete-account/send-otp', [AccountSecurityController::class, 'sendDeleteAccountOtp'])->middleware('throttle:20,1')->name('profile.delete.send-otp');
    Route::delete('/profile/delete-account', [AccountSecurityController::class, 'destroyAccount'])->middleware('throttle:20,1')->name('profile.delete.destroy');
});
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

// halaman hub minigame, leaderboard, password checker & WA/APK simulator publik, sedangkan arena kuis wajib login
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
Route::get('/minigame', [MinigameController::class, 'index'])->name('minigame.index');
Route::get('/minigame/password-checker', [MinigameController::class, 'passwordChecker'])->name('minigame.password-checker');
Route::get('/minigame/wa-simulator', [WaSimulatorController::class, 'show'])->name('minigame.wa-simulator');
// wa simulator
Route::post('/minigame/wa-simulator/record', [WaSimulatorController::class, 'recordResult'])->middleware('throttle:30,1')->name('minigame.wa-simulator.record');
// link scanner
Route::get('/minigame/link-scanner', [LinkScannerController::class, 'show'])->name('minigame.link-scanner');
Route::post('/minigame/link-scanner/scan', [LinkScannerController::class, 'scan'])->middleware('throttle:30,1')->name('minigame.link-scanner.scan');
Route::get('/minigame/panic-simulator', [PanicSimulatorController::class, 'show'])->name('minigame.panic-simulator');
// hp simulator
Route::post('/minigame/panic-simulator/record', [PanicSimulatorController::class, 'recordResult'])->middleware('throttle:30,1')->name('minigame.panic-simulator.record');

// tantangan harian & weekly streak (api + freeze 24 jam + 2x pemulihan)
Route::get('/streak/status', [DailyStreakController::class, 'status'])->name('streak.status');
Route::post('/streak/answer', [DailyStreakController::class, 'answer'])->middleware(['auth', 'throttle:20,1'])->name('streak.answer');

Route::middleware('auth')->group(function () {
    Route::post('/minigame/save-score', [MinigameController::class, 'saveScore'])->middleware('throttle:15,1')->name('minigame.save-score');
    Route::get('/minigame/{type}', [MinigameController::class, 'play'])->name('minigame.play');

    // rute pertemanan & live chat antar pengguna
    Route::get('/chat/unread-count', [SocialChatController::class, 'unreadCount'])->name('chat.unread');
    Route::get('/chat/summary', [SocialChatController::class, 'summary'])->name('chat.summary');
    Route::get('/chat/users', [SocialChatController::class, 'searchUsers'])->name('chat.users');
    Route::get('/chat/messages/{user}', [SocialChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/messages/{user}', [SocialChatController::class, 'sendMessage'])->middleware('throttle:30,1')->name('chat.send');

    Route::post('/friends/request/{user}', [SocialChatController::class, 'sendFriendRequest'])->middleware('throttle:20,1')->name('friends.request');
    Route::post('/friends/accept/{user}', [SocialChatController::class, 'acceptFriendRequest'])->middleware('throttle:20,1')->name('friends.accept');
    Route::post('/friends/decline/{user}', [SocialChatController::class, 'declineFriendRequest'])->middleware('throttle:20,1')->name('friends.decline');
    Route::delete('/friends/{user}', [SocialChatController::class, 'removeFriendship'])->middleware('throttle:20,1')->name('friends.remove');

    // rute follow / unfollow pengguna
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->middleware('throttle:30,1')->name('users.follow.toggle');
});

// daftar followers & following publik untuk modal profil
Route::get('/users/{user}/follows', [FollowController::class, 'list'])->name('users.follows.list');

// rute halaman forum komunitas & simpan jawaban + voting realtime ajax
Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/live-data', [ForumController::class, 'liveData'])->name('forum.live-data');
Route::post('/forum', [ForumController::class, 'store'])->middleware(['auth', 'throttle:20,1'])->name('forum.store');
Route::put('/forum/{forum}', [ForumController::class, 'update'])->middleware(['auth', 'throttle:20,1'])->name('forum.update');
Route::delete('/forum/{forum}', [ForumController::class, 'destroy'])->middleware(['auth', 'throttle:20,1'])->name('forum.destroy');
Route::post('/forum/{forum}/jawaban', [ForumController::class, 'storeJawaban'])->middleware(['auth', 'throttle:20,1'])->name('forum.jawaban.store');
Route::delete('/forum/jawaban/{jawaban}', [ForumController::class, 'destroyJawaban'])->middleware(['auth', 'throttle:20,1'])->name('forum.jawaban.destroy');
Route::post('/forum/jawaban/{jawaban}/vote', [ForumController::class, 'voteJawaban'])->middleware(['auth', 'throttle:30,1'])->name('forum.jawaban.vote');

// rute halaman testimoni & simpan ulasan (wajib login + rate limit anti-spam)
Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni.index');
Route::post('/testimoni', [TestimoniController::class, 'store'])->middleware(['auth', 'throttle:5,1'])->name('testimoni.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-otp', [AuthController::class, 'sendRegisterOtp'])->middleware('throttle:20,1')->name('register.otp.send');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});

// reset password via OTP Gmail (dapat diakses guest maupun user login)
Route::get('/lupa-password', [AccountSecurityController::class, 'showForgotPassword'])->name('password.otp.request');
Route::post('/lupa-password/send-otp', [AccountSecurityController::class, 'sendPasswordResetOtp'])->middleware('throttle:20,1')->name('password.otp.send');
Route::post('/lupa-password/reset', [AccountSecurityController::class, 'resetPasswordWithOtp'])->middleware('throttle:20,1')->name('password.otp.reset');

// rute OAuth Google di luar guest middleware agar mode=mobile (APK) selalu lancar
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// halaman kustomisasi profil khusus akun yang masuk melalui Google (tanpa navbar)
Route::middleware('auth')->group(function () {
    Route::get('/auth/google/customize', [AuthController::class, 'showGoogleCustomize'])->name('auth.google.customize');
    Route::post('/auth/google/customize', [AuthController::class, 'storeGoogleCustomize'])->middleware('throttle:15,1')->name('auth.google.customize.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// rute panel admin (hanya untuk role admin dan owner)
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminEdukasiController::class, 'index'])->name('dashboard');

        // tab 1: manajemen edukasi & evaluasi request tema dari user
        Route::get('/edukasi', [AdminEdukasiController::class, 'index'])->name('edukasi.index');
        Route::post('/edukasi', [AdminEdukasiController::class, 'store'])->name('edukasi.store');
        Route::put('/edukasi/{edukasi}', [AdminEdukasiController::class, 'update'])->name('edukasi.update');
        Route::delete('/edukasi/{edukasi}', [AdminEdukasiController::class, 'destroy'])->name('edukasi.destroy');
        Route::patch('/edukasi/requests/{edukasiRequest}/approve', [AdminEdukasiController::class, 'approveRequest'])->name('edukasi.request.approve');
        Route::patch('/edukasi/requests/{edukasiRequest}/reject', [AdminEdukasiController::class, 'rejectRequest'])->name('edukasi.request.reject');

        // tab 2: soal & level minigame
        Route::get('/minigame', [AdminMinigameController::class, 'index'])->name('minigame.index');
        Route::post('/minigame', [AdminMinigameController::class, 'store'])->name('minigame.store');
        Route::put('/minigame/{minigame}', [AdminMinigameController::class, 'update'])->name('minigame.update');
        Route::delete('/minigame/{minigame}', [AdminMinigameController::class, 'destroy'])->name('minigame.destroy');

        // kelola level minigame oleh admin
        Route::post('/minigame/levels', [AdminMinigameController::class, 'storeLevel'])->name('minigame.level.store');
        Route::put('/minigame/levels/{level}', [AdminMinigameController::class, 'updateLevel'])->name('minigame.level.update');
        Route::delete('/minigame/levels/{level}', [AdminMinigameController::class, 'destroyLevel'])->name('minigame.level.destroy');

        // tab 3: bank soal tantangan harian (daily streak)
        Route::get('/streak', [AdminStreakQuestionController::class, 'index'])->name('streak.index');
        Route::post('/streak', [AdminStreakQuestionController::class, 'store'])->name('streak.store');
        Route::put('/streak/{streakQuestion}', [AdminStreakQuestionController::class, 'update'])->name('streak.update');
        Route::patch('/streak/{streakQuestion}/toggle', [AdminStreakQuestionController::class, 'toggleActive'])->name('streak.toggle');
        Route::delete('/streak/{streakQuestion}', [AdminStreakQuestionController::class, 'destroy'])->name('streak.destroy');

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

    if (
        ! $baseDir
        || ! $fullPath
        || ! str_starts_with($fullPath, $baseDir . DIRECTORY_SEPARATOR)
        || ! is_file($fullPath)
    ) {
        abort(404);
    }

    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    if (in_array($ext, ['php', 'phtml', 'phar', 'html', 'htm', 'svg', 'js'], true)) {
        abort(403);
    }

    return response()->file($fullPath, [
        'X-Content-Type-Options' => 'nosniff',
    ]);
})->where('path', '.*')->name('storage.fallback');
