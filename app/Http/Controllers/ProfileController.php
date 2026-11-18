<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\Forum;
use App\Models\JawabanForum;
use App\Models\SkorGame;
use App\Models\Testimoni;
use App\Models\User;
use App\Services\SocialSchemaHelper;
use App\Services\UploadValidationHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        // tampilkan halaman profil milik pengguna yang sedang login
        return $this->renderProfile(Auth::user());
    }

    public function show(string $identifier): View
    {
        // cari pengguna berdasarkan id atau username
        $profileUser = User::where('id', $identifier)
            ->orWhere('username', $identifier)
            ->firstOrFail();

        return $this->renderProfile($profileUser);
    }

    public function update(Request $request): RedirectResponse
    {
        SocialSchemaHelper::ensureReady();
        $user = Auth::user();

        // validasi input edit profil termasuk foto avatar dan banner (mendukung gif/foto)
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'specialty_role' => ['nullable', 'string', 'max:60'],
            'bio' => ['nullable', 'string', 'max:350'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => UploadValidationHelper::imageRules(3072),
            'banner' => UploadValidationHelper::imageRules(5120),
        ]);

        $data = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'specialty_role' => filled($validated['specialty_role'] ?? null) ? trim($validated['specialty_role']) : null,
            'bio' => $validated['bio'] ?? $user->bio,
        ];

        // hash password baru jika diisi
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // upload foto profil ke public/images/avatars dan storage
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $targetDir = public_path('images/avatars');
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $file->move($targetDir, $filename);

            $storageDir = storage_path('app/public/avatars');
            if (! is_dir($storageDir)) {
                mkdir($storageDir, 0777, true);
            }
            @copy($targetDir . '/' . $filename, $storageDir . '/' . $filename);

            $data['avatar'] = 'images/avatars/' . $filename;
        }

        // upload banner profil (mendukung gif maupun foto biasa)
        if ($request->hasFile('banner')) {
            $bannerFile = $request->file('banner');
            $bannerName = 'banner_' . $user->id . '_' . time() . '.' . $bannerFile->getClientOriginalExtension();

            $bannerDir = public_path('images/banners');
            if (! is_dir($bannerDir)) {
                mkdir($bannerDir, 0777, true);
            }

            $bannerFile->move($bannerDir, $bannerName);

            $storageBannerDir = storage_path('app/public/banners');
            if (! is_dir($storageBannerDir)) {
                mkdir($storageBannerDir, 0777, true);
            }
            @copy($bannerDir . '/' . $bannerName, $storageBannerDir . '/' . $bannerName);

            $data['banner'] = 'images/banners/' . $bannerName;
        }

        $user->update($data);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profil dan tampilan banner Anda berhasil diperbarui.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:id,en'],
            'theme' => ['required', 'string', 'in:light,dark'],
        ]);

        $privacy = [
            'is_private_profile' => $request->boolean('is_private_profile'),
            'hide_location' => $request->boolean('hide_location'),
            'hide_email' => $request->boolean('hide_email'),
            'hide_minigame_stats' => $request->boolean('hide_minigame_stats'),
            'hide_badges' => $request->boolean('hide_badges'),
            'hide_activity' => $request->boolean('hide_activity'),
        ];

        $user->update([
            'locale' => $validated['locale'],
            'theme' => $validated['theme'],
            'privacy_settings' => $privacy,
        ]);

        session([
            'locale' => $validated['locale'],
            'theme' => $validated['theme'],
        ]);
        App::setLocale($validated['locale']);

        return redirect()
            ->route('profile.edit')
            ->with('success', __('ui.profile.settings_saved'));
    }

    protected function renderProfile(User $profileUser): View
    {
        SocialSchemaHelper::ensureReady();
        $isOwnProfile = Auth::check() && Auth::id() === $profileUser->id;
        $privacy = $profileUser->privacy_config;
        $isPrivateForVisitor = ! $isOwnProfile && $privacy['is_private_profile'];
        $friendRelation = Auth::check() && ! $isOwnProfile
            ? Friendship::relationFor(Auth::id(), (int) $profileUser->id)['status']
            : 'none';

        // hitung statistik kontribusi user
        $totalSkor = (int) SkorGame::where('user_id', $profileUser->id)->sum('skor');
        $topSkor = (int) SkorGame::where('user_id', $profileUser->id)->max('skor_tertinggi');
        $totalMain = SkorGame::where('user_id', $profileUser->id)->count();

        $totalUlasan = Testimoni::where('user_id', $profileUser->id)->count();
        $totalThread = Forum::where('user_id', $profileUser->id)->count();
        $totalJawaban = JawabanForum::where('user_id', $profileUser->id)->count();
        $totalDiskusi = $totalThread + $totalJawaban;

        $badges = $profileUser->badges_list;

        $friendsList = Friendship::with(['requester', 'addressee'])
            ->where('status', 'accepted')
            ->where(function ($q) use ($profileUser) {
                $q->where('requester_id', $profileUser->id)
                    ->orWhere('addressee_id', $profileUser->id);
            })
            ->get()
            ->map(fn (Friendship $f) => (int) $f->requester_id === (int) $profileUser->id ? $f->addressee : $f->requester)
            ->filter()
            ->values();
        $friendsCount = $friendsList->count();

        return view('profile.show', compact(
            'profileUser',
            'isOwnProfile',
            'privacy',
            'isPrivateForVisitor',
            'totalSkor',
            'topSkor',
            'totalMain',
            'totalUlasan',
            'totalDiskusi',
            'totalThread',
            'totalJawaban',
            'badges',
            'friendRelation',
            'friendsList',
            'friendsCount'
        ));
    }
}
