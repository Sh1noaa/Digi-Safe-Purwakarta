<?php

namespace App\Providers;

use App\Models\EdukasiRequest;
use App\Models\Forum;
use App\Models\MinigameLevel;
use App\Models\ModulEdukasi;
use App\Models\SoalMinigame;
use App\Models\User;
use App\Services\SocialSchemaHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Pastikan service MySQL XAMPP berjalan otomatis jika port 3306 belum aktif di Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $fp = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 0.25);
            if ($fp) {
                fclose($fp);
            } elseif (file_exists('C:\\xampp\\mysql\\bin\\mysqld.exe')) {
                foreach (glob('C:\\xampp\\mysql\\data\\*.info') ?: [] as $corruptInfo) {
                    @unlink($corruptInfo);
                }
                @pclose(@popen('start /B "" "C:\\xampp\\mysql\\bin\\mysqld.exe" --defaults-file="C:\\xampp\\mysql\\bin\\my.ini" --standalone >NUL 2>&1', 'r'));
                for ($i = 0; $i < 8; $i++) {
                    usleep(250000);
                    $check = @fsockopen('127.0.0.1', 3306, $e1, $e2, 0.2);
                    if ($check) {
                        fclose($check);
                        break;
                    }
                }
            }
        }
    }

    public function boot(): void
    {
        if (app()->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        User::ensurePreferenceColumns();
        MinigameLevel::ensureInitialized();
        SocialSchemaHelper::ensureReady();

        // bagikan statistik ringkas untuk panel kanan admin
        View::composer('admin.*', function ($view) {
            $pendingEdukasiRequests = Schema::hasTable('edukasi_requests')
                ? EdukasiRequest::where('status', 'pending')->count()
                : 0;

            $view->with('adminStats', [
                'total_edukasi' => ModulEdukasi::count(),
                'edukasi_teks' => ModulEdukasi::where('type', 'teks')->count(),
                'edukasi_link' => ModulEdukasi::where('type', '!=', 'teks')->orWhereNull('type')->count(),
                'pending_edukasi_requests' => $pendingEdukasiRequests,
                'total_soal' => SoalMinigame::count(),
                'total_levels' => MinigameLevel::count(),
                'total_forum' => Forum::count(),
                'forum_starred' => Forum::where('is_starred', true)->count(),
                'total_users' => User::humans()->count(),
                'total_admins' => User::humans()->whereIn('role', ['owner', 'admin'])->count(),
            ]);
        });
    }
}
