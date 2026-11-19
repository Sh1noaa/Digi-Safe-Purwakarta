<?php

namespace App\Services;

use App\Models\Testimoni;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminAnalyticsService
{
    /**
     * Susun data analitik real-time untuk diagram pertumbuhan pengguna
     * dan distribusi/tren rating testimoni (Bintang 5 sampai 1) di Panel Admin.
     */
    public static function build(?Carbon $referenceNow = null): array
    {
        $now = ($referenceNow ? $referenceNow->copy() : now())->timezone('Asia/Jakarta');

        $shortMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $fullMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $users = Schema::hasTable('users')
            ? User::humans()->get(['id', 'created_at', 'role'])
            : collect();

        $userTimestamps = $users->map(function ($u) use ($now) {
            if (! $u->created_at) {
                return $now->copy();
            }

            return Carbon::parse($u->created_at)->timezone('Asia/Jakarta');
        });

        $totalUsers = $userTimestamps->count();

        $monthlyBuckets = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->startOfMonth()->subMonths($i);
            $mEnd = $mStart->copy()->endOfMonth();
            $mNum = (int) $mStart->format('n');

            $newCount = $userTimestamps->filter(
                fn (Carbon $dt) => $dt->betweenIncluded($mStart, $mEnd)
            )->count();

            $cumCount = $userTimestamps->filter(
                fn (Carbon $dt) => $dt->lte($mEnd)
            )->count();

            $monthlyBuckets[] = [
                'key' => $mStart->format('Y-m'),
                'label' => $shortMonths[$mNum],
                'full_label' => $fullMonths[$mNum] . ' ' . $mStart->format('Y'),
                'count' => $newCount,
                'cumulative' => $cumCount,
            ];
        }

        $currMonthUsers = $monthlyBuckets[5]['count'] ?? 0;
        $prevMonthUsers = $monthlyBuckets[4]['count'] ?? 0;
        $monthUserDelta = $currMonthUsers - $prevMonthUsers;
        $monthUserDirection = $monthUserDelta > 0 ? 'up' : ($monthUserDelta < 0 ? 'down' : 'flat');
        $monthUserDeltaPct = $prevMonthUsers > 0
            ? round(($monthUserDelta / $prevMonthUsers) * 100, 1)
            : ($currMonthUsers > 0 ? 100.0 : 0.0);

        $dailyBuckets = [];
        for ($i = 6; $i >= 0; $i--) {
            $dStart = $now->copy()->startOfDay()->subDays($i);
            $dEnd = $dStart->copy()->endOfDay();
            $mNum = (int) $dStart->format('n');

            $newCount = $userTimestamps->filter(
                fn (Carbon $dt) => $dt->betweenIncluded($dStart, $dEnd)
            )->count();

            $cumCount = $userTimestamps->filter(
                fn (Carbon $dt) => $dt->lte($dEnd)
            )->count();

            $dailyBuckets[] = [
                'key' => $dStart->format('Y-m-d'),
                'label' => $dStart->format('d') . ' ' . $shortMonths[$mNum],
                'full_label' => $dStart->format('d') . ' ' . $fullMonths[$mNum] . ' ' . $dStart->format('Y'),
                'count' => $newCount,
                'cumulative' => $cumCount,
            ];
        }

        $curr7dStart = $now->copy()->startOfDay()->subDays(6);
        $curr7dEnd = $now->copy()->endOfDay();
        $prev7dStart = $now->copy()->startOfDay()->subDays(13);
        $prev7dEnd = $now->copy()->startOfDay()->subDays(7)->endOfDay();

        $curr7dUsers = $userTimestamps->filter(
            fn (Carbon $dt) => $dt->betweenIncluded($curr7dStart, $curr7dEnd)
        )->count();
        $prev7dUsers = $userTimestamps->filter(
            fn (Carbon $dt) => $dt->betweenIncluded($prev7dStart, $prev7dEnd)
        )->count();

        $dailyUserDelta = $curr7dUsers - $prev7dUsers;
        $dailyUserDirection = $dailyUserDelta > 0 ? 'up' : ($dailyUserDelta < 0 ? 'down' : 'flat');
        $dailyUserDeltaPct = $prev7dUsers > 0
            ? round(($dailyUserDelta / $prev7dUsers) * 100, 1)
            : ($curr7dUsers > 0 ? 100.0 : 0.0);

        $monthlyChart = self::computeSvgCoordinates($monthlyBuckets);
        $dailyChart = self::computeSvgCoordinates($dailyBuckets);

        $testimonis = Schema::hasTable('testimoni')
            ? Testimoni::query()
                ->whereIn('status_approval', ['approved', 'pending'])
                ->get(['id', 'rating', 'created_at'])
            : collect();

        $normalizedTestimonis = $testimonis->map(function ($t) use ($now) {
            $rating = max(1, min(5, (int) ($t->rating ?: 5)));
            $dt = $t->created_at
                ? Carbon::parse($t->created_at)->timezone('Asia/Jakarta')
                : $now->copy();

            return [
                'rating' => $rating,
                'created_at' => $dt,
            ];
        });

        $totalTestimoni = $normalizedTestimonis->count();
        $avgRating = $totalTestimoni > 0
            ? round((float) $normalizedTestimonis->avg('rating'), 2)
            : 0.0;

        $currMonthStart = $now->copy()->startOfMonth();
        $currMonthEnd = $now->copy()->endOfMonth();
        $prevMonthStart = $now->copy()->startOfMonth()->subMonth();
        $prevMonthEnd = $prevMonthStart->copy()->endOfMonth();

        $rawTotalsByStar = [];
        for ($star = 5; $star >= 1; $star--) {
            $rawTotalsByStar[$star] = $normalizedTestimonis->where('rating', $star)->count();
        }
        $maxStarCount = max(1, max($rawTotalsByStar));

        $stars = [];
        $monthlyRisingStars = [];
        $dailyRisingStars = [];

        for ($star = 5; $star >= 1; $star--) {
            $starItems = $normalizedTestimonis->where('rating', $star);
            $starTotal = $starItems->count();
            $sharePct = $totalTestimoni > 0 ? round(($starTotal / $totalTestimoni) * 100, 1) : 0.0;
            $barPct = $totalTestimoni > 0 ? round(($starTotal / $maxStarCount) * 100, 1) : 0.0;

            $mCurr = $starItems->filter(
                fn (array $item) => $item['created_at']->betweenIncluded($currMonthStart, $currMonthEnd)
            )->count();
            $mPrev = $starItems->filter(
                fn (array $item) => $item['created_at']->betweenIncluded($prevMonthStart, $prevMonthEnd)
            )->count();
            $mDelta = $mCurr - $mPrev;
            $mDir = $mDelta > 0 ? 'up' : ($mDelta < 0 ? 'down' : 'flat');

            $dCurr = $starItems->filter(
                fn (array $item) => $item['created_at']->betweenIncluded($curr7dStart, $curr7dEnd)
            )->count();
            $dPrev = $starItems->filter(
                fn (array $item) => $item['created_at']->betweenIncluded($prev7dStart, $prev7dEnd)
            )->count();
            $dDelta = $dCurr - $dPrev;
            $dDir = $dDelta > 0 ? 'up' : ($dDelta < 0 ? 'down' : 'flat');

            if ($mDelta > 0) {
                $monthlyRisingStars[] = [
                    'star' => $star,
                    'delta' => $mDelta,
                    'current' => $mCurr,
                ];
            }
            if ($dDelta > 0) {
                $dailyRisingStars[] = [
                    'star' => $star,
                    'delta' => $dDelta,
                    'current' => $dCurr,
                ];
            }

            $stars[$star] = [
                'star' => $star,
                'label' => 'Bintang ' . $star,
                'total' => $starTotal,
                'share_pct' => $sharePct,
                'bar_pct' => $barPct,
                'monthly' => [
                    'current' => $mCurr,
                    'previous' => $mPrev,
                    'delta' => $mDelta,
                    'direction' => $mDir,
                ],
                'daily' => [
                    'current' => $dCurr,
                    'previous' => $dPrev,
                    'delta' => $dDelta,
                    'direction' => $dDir,
                ],
            ];
        }

        usort($monthlyRisingStars, fn (array $a, array $b) => ($b['delta'] <=> $a['delta']) ?: ($b['star'] <=> $a['star']));
        usort($dailyRisingStars, fn (array $a, array $b) => ($b['delta'] <=> $a['delta']) ?: ($b['star'] <=> $a['star']));

        $dominantStar = 5;
        $dominantCount = -1;
        foreach ($rawTotalsByStar as $starKey => $cnt) {
            if ($cnt > $dominantCount) {
                $dominantCount = $cnt;
                $dominantStar = $starKey;
            }
        }

        $monthlyTestiCurrTotal = array_sum(array_map(fn ($s) => $s['monthly']['current'], $stars));
        $monthlyTestiPrevTotal = array_sum(array_map(fn ($s) => $s['monthly']['previous'], $stars));
        $dailyTestiCurrTotal = array_sum(array_map(fn ($s) => $s['daily']['current'], $stars));
        $dailyTestiPrevTotal = array_sum(array_map(fn ($s) => $s['daily']['previous'], $stars));

        return [
            'user_growth' => [
                'total_users' => $totalUsers,
                'monthly' => [
                    'period_label' => '6 Bulan Terakhir',
                    'comparison_label' => ($monthlyBuckets[5]['label'] ?? 'Bulan Ini') . ' vs ' . ($monthlyBuckets[4]['label'] ?? 'Bulan Lalu'),
                    'current_count' => $currMonthUsers,
                    'previous_count' => $prevMonthUsers,
                    'delta' => $monthUserDelta,
                    'delta_pct' => $monthUserDeltaPct,
                    'direction' => $monthUserDirection,
                    'buckets' => $monthlyChart['buckets'],
                    'max_new' => $monthlyChart['max_new'],
                    'max_cum' => $monthlyChart['max_cum'],
                    'polyline_new' => $monthlyChart['polyline_new'],
                    'area_new' => $monthlyChart['area_new'],
                    'polyline_cum' => $monthlyChart['polyline_cum'],
                ],
                'daily' => [
                    'period_label' => '7 Hari Terakhir',
                    'comparison_label' => '7 Hari Ini vs 7 Hari Lalu',
                    'current_count' => $curr7dUsers,
                    'previous_count' => $prev7dUsers,
                    'today_count' => $dailyBuckets[6]['count'] ?? 0,
                    'yesterday_count' => $dailyBuckets[5]['count'] ?? 0,
                    'delta' => $dailyUserDelta,
                    'delta_pct' => $dailyUserDeltaPct,
                    'direction' => $dailyUserDirection,
                    'buckets' => $dailyChart['buckets'],
                    'max_new' => $dailyChart['max_new'],
                    'max_cum' => $dailyChart['max_cum'],
                    'polyline_new' => $dailyChart['polyline_new'],
                    'area_new' => $dailyChart['area_new'],
                    'polyline_cum' => $dailyChart['polyline_cum'],
                ],
            ],
            'testimoni_ratings' => [
                'total' => $totalTestimoni,
                'avg_rating' => $avgRating,
                'dominant_star' => $dominantStar,
                'dominant_count' => max(0, $dominantCount),
                'stars' => array_values($stars),
                'monthly_summary' => [
                    'current_total' => $monthlyTestiCurrTotal,
                    'previous_total' => $monthlyTestiPrevTotal,
                    'delta_total' => $monthlyTestiCurrTotal - $monthlyTestiPrevTotal,
                    'rising_stars' => $monthlyRisingStars,
                    'headline' => self::buildStarHeadline($monthlyRisingStars, $dominantStar, max(0, $dominantCount), $totalTestimoni, 'bulan ini'),
                ],
                'daily_summary' => [
                    'current_total' => $dailyTestiCurrTotal,
                    'previous_total' => $dailyTestiPrevTotal,
                    'delta_total' => $dailyTestiCurrTotal - $dailyTestiPrevTotal,
                    'rising_stars' => $dailyRisingStars,
                    'headline' => self::buildStarHeadline($dailyRisingStars, $dominantStar, max(0, $dominantCount), $totalTestimoni, '7 hari ini'),
                ],
            ],
        ];
    }

    /**
     * Hitung titik koordinat SVG (viewBox 520x165) untuk grafik garis, area, dan batang.
     */
    private static function computeSvgCoordinates(array $buckets): array
    {
        $count = count($buckets);
        if ($count === 0) {
            return [
                'buckets' => [],
                'max_new' => 1,
                'max_cum' => 1,
                'polyline_new' => '',
                'area_new' => '',
                'polyline_cum' => '',
            ];
        }

        $maxNew = max(1, max(array_column($buckets, 'count')));
        $maxCum = max(1, max(array_column($buckets, 'cumulative')));

        $leftX = 46.0;
        $rightX = 484.0;
        $topY = 26.0;
        $bottomY = 128.0;
        $plotHeight = $bottomY - $topY;
        $stepX = $count > 1 ? ($rightX - $leftX) / ($count - 1) : 0.0;

        $pointsNew = [];
        $pointsCum = [];
        $enriched = [];

        foreach ($buckets as $idx => $b) {
            $x = round($leftX + ($idx * $stepX), 1);
            $ratioNew = $b['count'] / $maxNew;
            $ratioCum = $b['cumulative'] / $maxCum;

            $yNew = round($bottomY - ($ratioNew * $plotHeight), 1);
            $yCum = round($bottomY - ($ratioCum * $plotHeight), 1);
            $barHeight = round(max(4.0, $ratioNew * $plotHeight), 1);
            $barY = round($bottomY - $barHeight, 1);

            $pointsNew[] = "{$x},{$yNew}";
            $pointsCum[] = "{$x},{$yCum}";

            $b['x'] = $x;
            $b['y_new'] = $yNew;
            $b['y_cum'] = $yCum;
            $b['bar_y'] = $b['count'] > 0 ? $barY : ($bottomY - 3.0);
            $b['bar_h'] = $b['count'] > 0 ? $barHeight : 3.0;
            $b['bar_pct'] = round($ratioNew * 100, 1);

            $enriched[] = $b;
        }

        $polylineNew = implode(' ', $pointsNew);
        $polylineCum = implode(' ', $pointsCum);
        $firstX = $enriched[0]['x'];
        $lastX = $enriched[$count - 1]['x'];
        $areaNew = "{$firstX},{$bottomY} {$polylineNew} {$lastX},{$bottomY}";

        return [
            'buckets' => $enriched,
            'max_new' => $maxNew,
            'max_cum' => $maxCum,
            'polyline_new' => $polylineNew,
            'area_new' => $areaNew,
            'polyline_cum' => $polylineCum,
        ];
    }

    /**
     * Buat ringkasan teks bintang mana yang sedang menaik berdasarkan data asli.
     */
    private static function buildStarHeadline(
        array $risingStars,
        int $dominantStar,
        int $dominantCount,
        int $totalTestimoni,
        string $periodText
    ): string {
        if ($totalTestimoni === 0) {
            return 'Belum ada ulasan testimoni masuk';
        }

        if (! empty($risingStars)) {
            $parts = array_map(
                fn (array $r) => 'Bintang ' . $r['star'] . ' (+' . $r['delta'] . ')',
                array_slice($risingStars, 0, 2)
            );

            return implode(' & ', $parts) . ' naik pada ' . $periodText;
        }

        return 'Bintang ' . $dominantStar . ' mendominasi (' . $dominantCount . ' dari ' . $totalTestimoni . ' ulasan)';
    }
}
