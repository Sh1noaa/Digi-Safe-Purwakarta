import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';

class ProfileScreen extends StatefulWidget {
  final AppState appState;
  final int? userId; // null = profil user login sendiri

  const ProfileScreen({super.key, required this.appState, this.userId});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  UserProfile? _profile;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final path = widget.userId == null ? '/profile' : '/users/${widget.userId}';
      final res = await widget.appState.api.get(path);
      if (res['user'] is Map<String, dynamic>) {
        setState(() {
          _profile = UserProfile.fromJson(res['user'] as Map<String, dynamic>);
        });
      }
    } catch (e) {
      if (widget.userId == null && widget.appState.user != null) {
        setState(() => _profile = widget.appState.user);
      } else {
        setState(() => _error = e.toString());
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _openEditModal(UserProfile user) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _EditProfileSheet(
        appState: widget.appState,
        user: user,
        onSaved: _loadProfile,
      ),
    );
  }

  void _openSettingsModal(UserProfile user) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _SettingsSheet(
        appState: widget.appState,
        user: user,
        onSaved: _loadProfile,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;

    return Scaffold(
      backgroundColor: AppColors.softBg(isDark),
      appBar: AppBar(
        title: Text(
          widget.userId == null ? state.tr('my_profile') : (isEn ? 'Citizen Profile' : 'Profil Warga Siber'),
          style: AppTheme.heading(isDark, size: 16),
        ),
        actions: [
          if (_profile != null && _profile!.isOwnProfile)
            IconButton(
              icon: const Icon(Icons.settings_outlined),
              tooltip: state.tr('profile_settings'),
              onPressed: () => _openSettingsModal(_profile!),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadProfile,
        child: _loading
            ? Center(child: Text(state.tr('loading'), style: AppTheme.body(isDark)))
            : _error != null && _profile == null
                ? Center(child: Text(_error!, style: AppTheme.body(isDark, color: AppColors.danger)))
                : _buildContent(_profile!, isDark, isEn, state),
      ),
    );
  }

  Widget _buildContent(UserProfile profile, bool isDark, bool isEn, AppState state) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 40),
      children: [
        if (profile.isOwnProfile && profile.isPrivateProfile)
          Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFFFFBEB),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFFDE68A)),
            ),
            child: Row(
              children: [
                const Text('🔒 ', style: TextStyle(fontSize: 15)),
                Expanded(
                  child: Text(
                    isEn
                        ? 'Your account is set to Private Mode. Visitors only see your name, avatar & banner.'
                        : 'Akun Anda dalam Mode Privat. Pengunjung hanya melihat nama, avatar & banner.',
                    style: AppTheme.body(false, size: 11.5, weight: FontWeight.w600, color: const Color(0xFF92400E)),
                  ),
                ),
              ],
            ),
          ),

        // Kartu Profil Utama dengan aksen latar biru di belakang sesuai website
        Stack(
          clipBehavior: Clip.none,
          children: [
            Positioned(
              left: -4,
              top: 10,
              bottom: -6,
              width: 90,
              child: Container(
                decoration: BoxDecoration(
                  color: AppColors.sky,
                  borderRadius: BorderRadius.circular(28),
                ),
              ),
            ),
            Container(
              decoration: BoxDecoration(
                color: AppColors.cardBg(isDark),
                borderRadius: BorderRadius.circular(26),
                border: Border.all(color: AppColors.border(isDark)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.06),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              clipBehavior: Clip.antiAlias,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Banner
                  SizedBox(
                    height: 135,
                    width: double.infinity,
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        Container(
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              colors: [Color(0xFFF59E0B), Color(0xFFFB923C), Color(0xFFFDA4AF), Color(0xFF38BDF8)],
                            ),
                          ),
                        ),
                        if (profile.bannerUrl != null && profile.bannerUrl!.isNotEmpty)
                          Image.network(
                            profile.bannerUrl!,
                            fit: BoxFit.cover,
                            errorBuilder: (_, _, _) => const SizedBox.shrink(),
                          ),
                        if (profile.isPrivateForVisitor)
                          Positioned(
                            top: 12,
                            right: 12,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(
                                color: AppColors.ink.withValues(alpha: 0.85),
                                borderRadius: BorderRadius.circular(99),
                              ),
                              child: Text(
                                '🔒 ${state.tr('profile_private_badge')}',
                                style: AppTheme.heading(false, size: 10.5, color: AppColors.lime),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),

                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 0, 20, 22),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Transform.translate(
                          offset: const Offset(0, -34),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              UserAvatarWidget(
                                avatarUrl: profile.avatarUrl,
                                initial: profile.inisial,
                                nameSeed: profile.namaLengkap,
                                isOwner: profile.isOwner,
                                size: 78,
                                ringColor: AppColors.cardBg(isDark),
                              ),
                              if (profile.isOwnProfile)
                                Row(
                                  children: [
                                    _ActionChip(
                                      label: state.tr('profile_edit'),
                                      icon: Icons.edit_rounded,
                                      bg: AppColors.ink,
                                      fg: Colors.white,
                                      onTap: () => _openEditModal(profile),
                                    ),
                                    const SizedBox(width: 8),
                                    _ActionChip(
                                      label: state.tr('profile_settings'),
                                      icon: Icons.tune_rounded,
                                      bg: AppColors.softBg(isDark),
                                      fg: AppColors.textPrimary(isDark),
                                      border: AppColors.border(isDark),
                                      onTap: () => _openSettingsModal(profile),
                                    ),
                                  ],
                                ),
                            ],
                          ),
                        ),

                        Transform.translate(
                          offset: const Offset(0, -16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Wrap(
                                spacing: 8,
                                runSpacing: 6,
                                crossAxisAlignment: WrapCrossAlignment.center,
                                children: [
                                  Text(profile.namaLengkap, style: AppTheme.heading(isDark, size: 22)),
                                  RoleBadgeWidget(isOwner: profile.isOwner, isAdmin: profile.isAdmin),
                                ],
                              ),

                              if (profile.isPrivateForVisitor) ...[
                                const SizedBox(height: 16),
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(24),
                                  decoration: BoxDecoration(
                                    color: AppColors.softBg(isDark),
                                    borderRadius: BorderRadius.circular(18),
                                    border: Border.all(color: AppColors.border(isDark)),
                                  ),
                                  child: Column(
                                    children: [
                                      const Text('🔒', style: TextStyle(fontSize: 28)),
                                      const SizedBox(height: 8),
                                      Text(state.tr('profile_private_title'), style: AppTheme.heading(isDark, size: 15)),
                                      const SizedBox(height: 6),
                                      Text(
                                        state.tr('profile_private_desc'),
                                        textAlign: TextAlign.center,
                                        style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                                      ),
                                    ],
                                  ),
                                ),
                              ] else ...[
                                const SizedBox(height: 4),
                                Text(
                                  '${profile.roleLabel} • @${profile.username}',
                                  style: AppTheme.body(isDark, size: 12.5, weight: FontWeight.w500, color: AppColors.textSecondary(isDark)),
                                ),
                                const SizedBox(height: 8),
                                if (profile.locationHidden)
                                  Text('🔒 ${state.tr('profile_hidden')}', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)))
                                else
                                  Text(
                                    profile.bio ?? 'Purwakarta, Jawa Barat, Indonesia',
                                    style: AppTheme.body(isDark, size: 13, color: AppColors.textSecondary(isDark), height: 1.5),
                                  ),
                                if (profile.email != null && profile.email!.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(profile.email!, style: AppTheme.body(isDark, size: 11.5, color: AppColors.sky)),
                                ],

                                const SizedBox(height: 18),
                                Text(
                                  isEn ? 'ACHIEVEMENT BADGES' : 'LENCANA PENCAPAIAN',
                                  style: AppTheme.heading(isDark, size: 10.5, color: AppColors.textSecondary(isDark)),
                                ),
                                const SizedBox(height: 8),
                                if (profile.badgesHidden)
                                  Text('🔒 ${state.tr('profile_hidden')}', style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark)))
                                else if (profile.badges.isEmpty)
                                  Text(
                                    isEn ? 'Play minigames to unlock badges!' : 'Mainkan minigame untuk membuka lencana!',
                                    style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                                  )
                                else
                                  Wrap(
                                    spacing: 8,
                                    runSpacing: 8,
                                    children: profile.badges.map((b) {
                                      final isNewPlayer = b == 'New Player';
                                      return Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                        decoration: BoxDecoration(
                                          color: isNewPlayer
                                              ? AppColors.skySoft.withValues(alpha: isDark ? 0.2 : 1)
                                              : AppColors.lime.withValues(alpha: 0.38),
                                          borderRadius: BorderRadius.circular(99),
                                          border: Border.all(
                                            color: isNewPlayer ? AppColors.sky.withValues(alpha: 0.35) : AppColors.lime,
                                          ),
                                        ),
                                        child: Text(
                                          isNewPlayer ? '• $b' : '★ $b',
                                          style: AppTheme.heading(
                                            isDark,
                                            size: 11,
                                            color: isNewPlayer ? AppColors.sky : (isDark ? AppColors.lime : AppColors.ink),
                                          ),
                                        ),
                                      );
                                    }).toList(),
                                  ),

                                const SizedBox(height: 20),
                                // 3 Kotak Statistik Profil
                                Row(
                                  children: [
                                    Expanded(
                                      child: _ProfileStatTile(
                                        isDark: isDark,
                                        title: profile.stats.minigameHidden
                                            ? '🔒'
                                            : '${profile.stats.totalSkor ?? 0} pts',
                                        sub: isEn ? 'Minigame Score' : 'Total Skor',
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: _ProfileStatTile(
                                        isDark: isDark,
                                        title: profile.stats.activityHidden
                                            ? '🔒'
                                            : '${profile.stats.totalDiskusi ?? 0}',
                                        sub: isEn ? 'Forum Posts' : 'Diskusi Forum',
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: _ProfileStatTile(
                                        isDark: isDark,
                                        title: profile.stats.activityHidden
                                            ? '🔒'
                                            : '${profile.stats.totalUlasan ?? 0}',
                                        sub: isEn ? 'Reviews' : 'Ulasan',
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),

        if (profile.isOwnProfile) ...[
          const SizedBox(height: 20),
          OutlinedButton.icon(
            onPressed: () async {
              await state.logout();
              if (mounted) Navigator.of(context).pop();
            },
            icon: const Icon(Icons.logout_rounded, color: AppColors.danger, size: 18),
            label: Text(state.tr('logout'), style: AppTheme.heading(isDark, size: 12.5, color: AppColors.danger)),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 14),
              side: BorderSide(color: AppColors.danger.withValues(alpha: 0.35)),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
            ),
          ),
        ],
      ],
    );
  }
}

class _ActionChip extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color bg;
  final Color fg;
  final Color? border;
  final VoidCallback onTap;

  const _ActionChip({
    required this.label,
    required this.icon,
    required this.bg,
    required this.fg,
    this.border,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(99),
          border: border != null ? Border.all(color: border!) : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: fg),
            const SizedBox(width: 5),
            Text(label, style: TextStyle(color: fg, fontSize: 11.5, fontWeight: FontWeight.w700)),
          ],
        ),
      ),
    );
  }
}

class _ProfileStatTile extends StatelessWidget {
  final bool isDark;
  final String title;
  final String sub;

  const _ProfileStatTile({required this.isDark, required this.title, required this.sub});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.softBg(isDark),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border(isDark)),
      ),
      child: Column(
        children: [
          Text(title, style: AppTheme.heading(isDark, size: 16)),
          const SizedBox(height: 2),
          Text(sub, textAlign: TextAlign.center, style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
        ],
      ),
    );
  }
}

class _EditProfileSheet extends StatefulWidget {
  final AppState appState;
  final UserProfile user;
  final VoidCallback onSaved;

  const _EditProfileSheet({
    required this.appState,
    required this.user,
    required this.onSaved,
  });

  @override
  State<_EditProfileSheet> createState() => _EditProfileSheetState();
}

class _EditProfileSheetState extends State<_EditProfileSheet> {
  late final TextEditingController _nameCtrl;
  late final TextEditingController _bioCtrl;
  final _passCtrl = TextEditingController();
  File? _avatarFile;
  File? _bannerFile;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _nameCtrl = TextEditingController(text: widget.user.namaLengkap);
    _bioCtrl = TextEditingController(text: widget.user.bio ?? '');
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _bioCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImage(bool isAvatar) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
    if (picked == null) return;
    setState(() {
      if (isAvatar) {
        _avatarFile = File(picked.path);
      } else {
        _bannerFile = File(picked.path);
      }
    });
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    try {
      await widget.appState.updateProfile(
        namaLengkap: _nameCtrl.text,
        bio: _bioCtrl.text,
        password: _passCtrl.text.isEmpty ? null : _passCtrl.text,
        avatarFile: _avatarFile,
        bannerFile: _bannerFile,
      );
      widget.onSaved();
      if (mounted) {
        Navigator.of(context).pop();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(widget.appState.isEn ? 'Profile updated!' : 'Profil berhasil diperbarui!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;

    return Container(
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 24),
      decoration: BoxDecoration(
        color: AppColors.cardBg(isDark),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.appState.tr('profile_edit'), style: AppTheme.heading(isDark, size: 18)),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickImage(true),
                    icon: const Icon(Icons.face_rounded, size: 16),
                    label: Text(_avatarFile != null ? 'Avatar ✓' : (isEn ? 'Pick Avatar' : 'Ganti Foto Profil'), style: const TextStyle(fontSize: 12)),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickImage(false),
                    icon: const Icon(Icons.panorama_rounded, size: 16),
                    label: Text(_bannerFile != null ? 'Banner ✓' : (isEn ? 'Pick Banner' : 'Ganti Banner'), style: const TextStyle(fontSize: 12)),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _nameCtrl,
              decoration: InputDecoration(
                labelText: isEn ? 'Display Name' : 'Nama Lengkap',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _bioCtrl,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: isEn ? 'Location / Bio' : 'Alamat / Bio Singkat',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _passCtrl,
              obscureText: true,
              decoration: InputDecoration(
                labelText: isEn ? 'New Password (optional)' : 'Password Baru (opsional)',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.ink,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                ),
                child: _saving
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.lime))
                    : Text(widget.appState.tr('save'), style: AppTheme.heading(false, size: 13, color: Colors.white)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SettingsSheet extends StatefulWidget {
  final AppState appState;
  final UserProfile user;
  final VoidCallback onSaved;

  const _SettingsSheet({
    required this.appState,
    required this.user,
    required this.onSaved,
  });

  @override
  State<_SettingsSheet> createState() => _SettingsSheetState();
}

class _SettingsSheetState extends State<_SettingsSheet> {
  late String _locale;
  late String _theme;
  late bool _isPrivate;
  late bool _hideLocation;
  late bool _hideEmail;
  late bool _hideStats;
  late bool _hideBadges;
  late bool _hideActivity;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _locale = widget.appState.locale;
    _theme = widget.appState.theme;
    final p = widget.user.privacySettings;
    _isPrivate = p.isPrivateProfile;
    _hideLocation = p.hideLocation;
    _hideEmail = p.hideEmail;
    _hideStats = p.hideMinigameStats;
    _hideBadges = p.hideBadges;
    _hideActivity = p.hideActivity;
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    try {
      await widget.appState.updateSettings(
        newLocale: _locale,
        newTheme: _theme,
        privacy: PrivacySettings(
          isPrivateProfile: _isPrivate,
          hideLocation: _hideLocation,
          hideEmail: _hideEmail,
          hideMinigameStats: _hideStats,
          hideBadges: _hideBadges,
          hideActivity: _hideActivity,
        ),
      );
      widget.onSaved();
      if (mounted) Navigator.of(context).pop();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = _theme == 'dark';
    final isEn = _locale == 'en';

    return Container(
      constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      decoration: BoxDecoration(
        color: AppColors.cardBg(isDark),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(widget.appState.tr('profile_settings'), style: AppTheme.heading(isDark, size: 18)),
              IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close_rounded)),
            ],
          ),
          Expanded(
            child: ListView(
              children: [
                Text(widget.appState.tr('profile_lang'), style: AppTheme.heading(isDark, size: 13)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: _ChoiceCard(
                        isDark: isDark,
                        selected: _locale == 'id',
                        title: '🇮🇩 Indonesia',
                        onTap: () => setState(() => _locale = 'id'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _ChoiceCard(
                        isDark: isDark,
                        selected: _locale == 'en',
                        title: '🇬🇧 English',
                        onTap: () => setState(() => _locale = 'en'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Text(widget.appState.tr('profile_theme'), style: AppTheme.heading(isDark, size: 13)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: _ChoiceCard(
                        isDark: isDark,
                        selected: _theme == 'light',
                        title: '☀️ ${isEn ? 'Light' : 'Terang'}',
                        onTap: () => setState(() => _theme = 'light'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _ChoiceCard(
                        isDark: isDark,
                        selected: _theme == 'dark',
                        title: '🌙 ${isEn ? 'Dark' : 'Gelap'}',
                        onTap: () => setState(() => _theme = 'dark'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Text(widget.appState.tr('profile_privacy'), style: AppTheme.heading(isDark, size: 13)),
                SwitchListTile(
                  value: _isPrivate,
                  onChanged: (v) => setState(() => _isPrivate = v),
                  title: Text(isEn ? 'Private Profile Mode' : 'Mode Profil Privat', style: AppTheme.body(isDark, weight: FontWeight.w600)),
                  subtitle: Text(isEn ? 'Show only name, avatar & banner to visitors' : 'Hanya tampilkan nama, foto profil & banner ke orang lain', style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark))),
                ),
                SwitchListTile(
                  value: _hideLocation,
                  onChanged: (v) => setState(() => _hideLocation = v),
                  title: Text(isEn ? 'Hide Address / Bio' : 'Sembunyikan Alamat / Bio', style: AppTheme.body(isDark)),
                ),
                SwitchListTile(
                  value: _hideEmail,
                  onChanged: (v) => setState(() => _hideEmail = v),
                  title: Text(isEn ? 'Hide Email Address' : 'Sembunyikan Email', style: AppTheme.body(isDark)),
                ),
                SwitchListTile(
                  value: _hideStats,
                  onChanged: (v) => setState(() => _hideStats = v),
                  title: Text(isEn ? 'Hide Minigame Scores' : 'Sembunyikan Skor Minigame', style: AppTheme.body(isDark)),
                ),
                SwitchListTile(
                  value: _hideBadges,
                  onChanged: (v) => setState(() => _hideBadges = v),
                  title: Text(isEn ? 'Hide Achievement Badges' : 'Sembunyikan Lencana', style: AppTheme.body(isDark)),
                ),
                SwitchListTile(
                  value: _hideActivity,
                  onChanged: (v) => setState(() => _hideActivity = v),
                  title: Text(isEn ? 'Hide Forum & Review Activity' : 'Sembunyikan Aktivitas Forum/Ulasan', style: AppTheme.body(isDark)),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _saving ? null : _save,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.sky,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
              ),
              child: _saving
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(widget.appState.tr('save'), style: AppTheme.heading(false, size: 13, color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }
}

class _ChoiceCard extends StatelessWidget {
  final bool isDark;
  final bool selected;
  final String title;
  final VoidCallback onTap;

  const _ChoiceCard({
    required this.isDark,
    required this.selected,
    required this.title,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: selected ? AppColors.sky.withValues(alpha: 0.14) : AppColors.softBg(isDark),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: selected ? AppColors.sky : AppColors.border(isDark), width: selected ? 1.8 : 1),
        ),
        child: Text(title, style: AppTheme.heading(isDark, size: 12.5, color: selected ? AppColors.sky : AppColors.textPrimary(isDark))),
      ),
    );
  }
}

class UsersDirectoryScreen extends StatefulWidget {
  final AppState appState;

  const UsersDirectoryScreen({super.key, required this.appState});

  @override
  State<UsersDirectoryScreen> createState() => _UsersDirectoryScreenState();
}

class _UsersDirectoryScreenState extends State<UsersDirectoryScreen> {
  final _searchCtrl = TextEditingController();
  List<UserProfile> _users = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load([String q = '']) async {
    setState(() => _loading = true);
    try {
      final path = q.trim().isEmpty ? '/users' : '/users?q=${Uri.encodeQueryComponent(q.trim())}';
      final res = await widget.appState.api.get(path);
      final raw = res['users'];
      if (raw is List) {
        setState(() {
          _users = raw.map((e) => UserProfile.fromJson(e as Map<String, dynamic>)).toList();
        });
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;

    return Scaffold(
      backgroundColor: AppColors.softBg(isDark),
      appBar: AppBar(
        title: Text(isEn ? 'Registered Cyber Citizens' : 'Direktori Warga Siber', style: AppTheme.heading(isDark, size: 16)),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchCtrl,
              onSubmitted: _load,
              decoration: InputDecoration(
                hintText: isEn ? 'Search user name or username...' : 'Cari nama atau username...',
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: IconButton(icon: const Icon(Icons.send_rounded), onPressed: () => _load(_searchCtrl.text)),
                filled: true,
                fillColor: AppColors.cardBg(isDark),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none),
              ),
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : ListView.separated(
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                    itemCount: _users.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 10),
                    itemBuilder: (_, idx) {
                      final u = _users[idx];
                      return InkWell(
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => ProfileScreen(appState: widget.appState, userId: u.id)),
                        ),
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.cardBg(isDark),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: AppColors.border(isDark)),
                          ),
                          child: Row(
                            children: [
                              UserAvatarWidget(
                                avatarUrl: u.avatarUrl,
                                initial: u.inisial,
                                nameSeed: u.namaLengkap,
                                isOwner: u.isOwner,
                                size: 46,
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Flexible(
                                          child: Text(u.namaLengkap, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 14)),
                                        ),
                                        const SizedBox(width: 6),
                                        RoleBadgeWidget(isOwner: u.isOwner, isAdmin: u.isAdmin, compact: true),
                                      ],
                                    ),
                                    Text(
                                      u.isPrivateForVisitor ? '🔒 ${widget.appState.tr('profile_private_badge')}' : '@${u.username}',
                                      style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark)),
                                    ),
                                  ],
                                ),
                              ),
                              const Icon(Icons.chevron_right_rounded, color: AppColors.sky),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

