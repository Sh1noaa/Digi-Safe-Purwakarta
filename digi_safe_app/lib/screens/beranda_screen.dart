import 'dart:async';

import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';
import 'auth_screens.dart';
import 'profile_screens.dart';

class BerandaScreen extends StatefulWidget {
  final AppState appState;

  const BerandaScreen({super.key, required this.appState});

  @override
  State<BerandaScreen> createState() => _BerandaScreenState();
}

class _BerandaScreenState extends State<BerandaScreen> {
  bool _loading = true;
  String? _error;
  int _lastLoadedVersion = -1;

  Map<String, dynamic> _aboutStats = {};
  List<Map<String, dynamic>> _services = [];
  Map<String, List<Map<String, dynamic>>> _faqGroups = {};
  List<UserProfile> _registeredUsers = [];

  String _activeFaqTab = 'pelajar';
  int? _openFaqIndex; // Selalu tertutup saat berpindah kategori tab (sesuai permintaan website!)

  final ScrollController _marqueeCtrl = ScrollController();
  Timer? _marqueeTimer;

  @override
  void initState() {
    super.initState();
    _loadData();
    _startMarquee();
  }

  @override
  void didUpdateWidget(covariant BerandaScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.appState.contentVersion != _lastLoadedVersion) {
      _loadData();
    }
  }

  @override
  void dispose() {
    _marqueeTimer?.cancel();
    _marqueeCtrl.dispose();
    super.dispose();
  }

  void _startMarquee() {
    _marqueeTimer = Timer.periodic(const Duration(milliseconds: 45), (_) {
      if (!mounted || !_marqueeCtrl.hasClients) return;
      final maxScroll = _marqueeCtrl.position.maxScrollExtent;
      if (maxScroll <= 0) return;
      final next = _marqueeCtrl.offset + 0.9;
      if (next >= maxScroll) {
        _marqueeCtrl.jumpTo(0);
      } else {
        _marqueeCtrl.jumpTo(next);
      }
    });
  }

  Future<void> _loadData() async {
    _lastLoadedVersion = widget.appState.contentVersion;
    setState(() {
      _loading = _registeredUsers.isEmpty;
      _error = null;
    });

    try {
      final res = await widget.appState.api.get('/beranda');
      if (!mounted) return;
      setState(() {
        _aboutStats = Map<String, dynamic>.from(res['about_stats'] ?? {});
        _services = (res['services'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();

        final rawFaq = res['faq_groups'];
        if (rawFaq is Map) {
          _faqGroups = rawFaq.map((key, val) => MapEntry(
                key.toString(),
                (val as List).map((item) => Map<String, dynamic>.from(item as Map)).toList(),
              ));
        }

        final rawUsers = res['registered_users'];
        if (rawUsers is List) {
          _registeredUsers = rawUsers.map((e) => UserProfile.fromJson(e as Map<String, dynamic>)).toList();
        }
      });
    } catch (e) {
      if (mounted) setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          // 1. HERO LANGIT BIRU + KAWANAN BURUNG BERTERBANGAN
          Padding(
            padding: const EdgeInsets.fromLTRB(10, 10, 10, 0),
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(24),
                image: const DecorationImage(
                  image: AssetImage('assets/images/bluesky.jpeg'),
                  fit: BoxFit.cover,
                ),
              ),
              clipBehavior: Clip.antiAlias,
              child: Stack(
                children: [
                  Positioned.fill(
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [
                            const Color(0xFF105EB2).withValues(alpha: 0.88),
                            const Color(0xFF1E88E5).withValues(alpha: 0.52),
                            const Color(0xFF2196F3).withValues(alpha: 0.18),
                            const Color(0xFF1E88E5).withValues(alpha: 0.36),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const Positioned.fill(child: FlyingBirdsOverlay()),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 36, 20, 32),
                    child: Column(
                      children: [
                        if (state.isAuthenticated) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.96),
                              borderRadius: BorderRadius.circular(99),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  '${state.tr('logged_in_as')} ',
                                  style: AppTheme.body(false, size: 11.5, color: AppColors.muted),
                                ),
                                Flexible(
                                  child: Text(
                                    state.user!.namaLengkap,
                                    overflow: TextOverflow.ellipsis,
                                    style: AppTheme.heading(false, size: 12, color: AppColors.ink),
                                  ),
                                ),
                                const SizedBox(width: 6),
                                RoleBadgeWidget(
                                  isOwner: state.user!.isOwner,
                                  isAdmin: state.user!.isAdmin,
                                  compact: true,
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),
                        ],
                        Text(
                          state.tr('hero_title'),
                          textAlign: TextAlign.center,
                          style: AppTheme.heading(false, size: 28, color: Colors.white, height: 1.14),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          state.tr('hero_subtitle'),
                          textAlign: TextAlign.center,
                          style: AppTheme.body(false, size: 13.5, color: Colors.white.withValues(alpha: 0.92), height: 1.55),
                        ),
                        const SizedBox(height: 22),

                        // CTA Pills
                        Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          alignment: WrapAlignment.center,
                          children: [
                            GestureDetector(
                              onTap: () => state.setTab(1), // Langsung buka Edukasi
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                                decoration: BoxDecoration(
                                  color: AppColors.ink.withValues(alpha: 0.3),
                                  borderRadius: BorderRadius.circular(99),
                                  border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.menu_book_rounded, size: 15, color: AppColors.lime),
                                    const SizedBox(width: 7),
                                    Text(
                                      state.tr('explore_edu'),
                                      style: AppTheme.heading(false, size: 11, color: Colors.white),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            GestureDetector(
                              onTap: () {
                                if (state.isAuthenticated) {
                                  state.setTab(2); // Minigame
                                } else {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(builder: (_) => RegisterScreen(appState: state)),
                                  );
                                }
                              },
                              child: Container(
                                padding: const EdgeInsets.fromLTRB(20, 7, 7, 7),
                                decoration: BoxDecoration(
                                  color: AppColors.lime,
                                  borderRadius: BorderRadius.circular(99),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.14),
                                      blurRadius: 14,
                                      offset: const Offset(0, 6),
                                    ),
                                  ],
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      state.tr('start_now'),
                                      style: AppTheme.heading(false, size: 11.5, color: AppColors.ink),
                                    ),
                                    const SizedBox(width: 10),
                                    Container(
                                      width: 30,
                                      height: 30,
                                      decoration: const BoxDecoration(color: AppColors.ink, shape: BoxShape.circle),
                                      child: const Icon(Icons.north_east_rounded, size: 15, color: Colors.white),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 22),
                        Text(
                          state.tr('trusted_users'),
                          style: AppTheme.body(false, size: 12, weight: FontWeight.w500, color: Colors.white.withValues(alpha: 0.9)),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(
                            5,
                            (_) => const Icon(Icons.star_rounded, color: AppColors.lime, size: 18),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          if (_loading)
            const Padding(
              padding: EdgeInsets.all(24),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_error != null)
            Padding(
              padding: const EdgeInsets.all(20),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: const Color(0xFFFCA5A5)),
                ),
                child: Column(
                  children: [
                    Text(_error!, style: AppTheme.body(false, size: 12, color: AppColors.danger)),
                    const SizedBox(height: 8),
                    ElevatedButton(onPressed: _loadData, child: Text(state.tr('refresh'))),
                  ],
                ),
              ),
            )
          else ...[
            // 2. TENTANG KAMI STATS BENTO GRID
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 36, 16, 0),
              child: Column(
                children: [
                  DigiSectionKicker(text: state.tr('about_badge'), isDark: isDark),
                  const SizedBox(height: 10),
                  Text(
                    state.tr('about_title_1'),
                    textAlign: TextAlign.center,
                    style: AppTheme.heading(isDark, size: 22, height: 1.22),
                  ),
                  const SizedBox(height: 22),

                  // Sky Card: 120+ Mitra
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: AppColors.sky,
                      borderRadius: BorderRadius.circular(22),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('DIGI•SAFE', style: AppTheme.heading(false, size: 16, color: Colors.white)),
                            Container(
                              width: 34,
                              height: 34,
                              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
                              child: const Icon(Icons.bar_chart_rounded, color: AppColors.ink, size: 18),
                            ),
                          ],
                        ),
                        const SizedBox(height: 28),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.cardBg(isDark),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${_aboutStats['mitra_count'] ?? '120+'}',
                                style: AppTheme.heading(isDark, size: 28),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${_aboutStats['mitra_desc'] ?? ''}',
                                style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.5),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // 100% Commitment Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: AppColors.softBg(isDark),
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: AppColors.border(isDark)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${_aboutStats['komitmen_label'] ?? ''}',
                          style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${_aboutStats['komitmen_persen'] ?? '100%'}',
                          style: AppTheme.heading(isDark, size: 32),
                        ),
                        const SizedBox(height: 14),
                        Text(
                          '${_aboutStats['komitmen_quote'] ?? ''}',
                          style: AppTheme.body(isDark, size: 12.5, weight: FontWeight.w500, height: 1.55),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Lime & Ink Duo Cards
                  Row(
                    children: [
                      Expanded(
                        flex: 3,
                        child: Container(
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            color: AppColors.lime,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${_aboutStats['modul_label'] ?? ''}',
                                style: AppTheme.body(false, size: 11, weight: FontWeight.w600, color: AppColors.ink.withValues(alpha: 0.75)),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${_aboutStats['modul_interaksi'] ?? '520k+'}',
                                style: AppTheme.heading(false, size: 26, color: AppColors.ink),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        flex: 2,
                        child: Container(
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            color: AppColors.ink,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${_aboutStats['kecamatan_count'] ?? '20+'}',
                                style: AppTheme.heading(false, size: 24, color: Colors.white),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${_aboutStats['kecamatan_label'] ?? ''}',
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: AppTheme.body(false, size: 10.5, color: Colors.white.withValues(alpha: 0.75)),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // 3. LAYANAN KAMI
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 42, 16, 0),
              child: Column(
                children: [
                  DigiSectionKicker(text: state.tr('services_badge'), isDark: isDark),
                  const SizedBox(height: 10),
                  Text(
                    state.tr('services_title'),
                    textAlign: TextAlign.center,
                    style: AppTheme.heading(isDark, size: 22, height: 1.22),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    state.tr('services_desc'),
                    textAlign: TextAlign.center,
                    style: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark), height: 1.5),
                  ),
                  const SizedBox(height: 20),

                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.softBg(isDark),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: AppColors.border(isDark)),
                    ),
                    child: Column(
                      children: List.generate(_services.length, (i) {
                        final srv = _services[i];
                        final slug = (srv['slug'] ?? 'edukasi').toString();
                        final iconData = slug == 'minigame'
                            ? Icons.timer_rounded
                            : slug == 'forum'
                                ? Icons.groups_rounded
                                : Icons.menu_book_rounded;
                        final targetTab = slug == 'minigame' ? 2 : slug == 'forum' ? 3 : 1;

                        return GestureDetector(
                          onTap: () => state.setTab(targetTab),
                          child: Container(
                            margin: EdgeInsets.only(bottom: i == _services.length - 1 ? 0 : 10),
                            padding: const EdgeInsets.all(18),
                            decoration: BoxDecoration(
                              color: AppColors.cardBg(isDark),
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: AppColors.border(isDark)),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  width: 42,
                                  height: 42,
                                  decoration: BoxDecoration(
                                    color: AppColors.lime,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(iconData, color: AppColors.ink, size: 22),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text('${srv['title'] ?? ''}', style: AppTheme.heading(isDark, size: 16)),
                                      const SizedBox(height: 4),
                                      Text(
                                        '${srv['description'] ?? ''}',
                                        style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.5),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 6),
                                const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.sky),
                              ],
                            ),
                          ),
                        );
                      }),
                    ),
                  ),
                ],
              ),
            ),

            // 4. DIREKTORI WARGA SIBER TERDAFTAR (MARQUEE)
            if (_registeredUsers.isNotEmpty) ...[
              Container(
                margin: const EdgeInsets.only(top: 40),
                padding: const EdgeInsets.symmetric(vertical: 28),
                color: isDark ? AppColors.darkSurface : const Color(0xFFF6F8FB),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          DigiSectionKicker(text: state.tr('users_badge'), isDark: isDark),
                          const SizedBox(height: 8),
                          Text(state.tr('users_title'), style: AppTheme.heading(isDark, size: 21)),
                          const SizedBox(height: 6),
                          Text(
                            state.tr('users_desc', {'count': '${_registeredUsers.length}'}),
                            style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                          ),
                          const SizedBox(height: 14),
                          GestureDetector(
                            onTap: () => Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => UsersDirectoryScreen(appState: state)),
                            ),
                            child: Container(
                              padding: const EdgeInsets.fromLTRB(16, 7, 7, 7),
                              decoration: BoxDecoration(
                                color: AppColors.ink,
                                borderRadius: BorderRadius.circular(99),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    state.tr('view_all_users', {'count': '${_registeredUsers.length}'}),
                                    style: AppTheme.heading(false, size: 11, color: Colors.white),
                                  ),
                                  const SizedBox(width: 10),
                                  Container(
                                    width: 26,
                                    height: 26,
                                    decoration: const BoxDecoration(color: AppColors.lime, shape: BoxShape.circle),
                                    child: const Icon(Icons.north_east_rounded, size: 14, color: AppColors.ink),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),
                    SizedBox(
                      height: 148,
                      child: ListView.builder(
                        controller: _marqueeCtrl,
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: _registeredUsers.length * 2,
                        itemBuilder: (context, idx) {
                          final u = _registeredUsers[idx % _registeredUsers.length];
                          return GestureDetector(
                            onTap: () => Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => ProfileScreen(appState: state, userId: u.id),
                              ),
                            ),
                            child: Container(
                              width: 245,
                              margin: const EdgeInsets.only(right: 12),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: AppColors.cardBg(isDark),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: AppColors.border(isDark)),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      UserAvatarWidget(
                                        avatarUrl: u.avatarUrl,
                                        initial: u.inisial,
                                        nameSeed: u.namaLengkap,
                                        isOwner: u.isOwner,
                                        size: 42,
                                      ),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              u.namaLengkap,
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: AppTheme.heading(isDark, size: 13),
                                            ),
                                            Text(
                                              u.isPrivateForVisitor ? '🔒 ${state.tr('profile_private_badge')}' : '@${u.username}',
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark)),
                                            ),
                                          ],
                                        ),
                                      ),
                                      RoleBadgeWidget(isOwner: u.isOwner, isAdmin: u.isAdmin, compact: true),
                                    ],
                                  ),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        u.isPrivateForVisitor
                                            ? '🔒 Privat'
                                            : '${u.stats.totalSkor ?? 0} pts',
                                        style: AppTheme.heading(isDark, size: 11.5, color: AppColors.sky),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: AppColors.softBg(isDark),
                                          borderRadius: BorderRadius.circular(99),
                                        ),
                                        child: Text(
                                          isEn ? 'View Profile →' : 'Lihat Profil →',
                                          style: AppTheme.body(isDark, size: 10.5, weight: FontWeight.w600),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // 5. Q & A INTERAKTIF PALING BAWAH BERANDA
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 40, 16, 40),
              child: Column(
                children: [
                  RichText(
                    textAlign: TextAlign.center,
                    text: TextSpan(
                      style: AppTheme.heading(isDark, size: 26),
                      children: [
                        TextSpan(text: '${state.tr('faq_heading_1')} '),
                        TextSpan(text: state.tr('faq_heading_2'), style: const TextStyle(color: AppColors.sky)),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    state.tr('faq_subtitle'),
                    textAlign: TextAlign.center,
                    style: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark), height: 1.5),
                  ),
                  const SizedBox(height: 20),

                  // 3 Tab Kategori FAQ
                  Row(
                    children: [
                      Expanded(
                        child: _FaqTabCard(
                          isDark: isDark,
                          active: _activeFaqTab == 'pelajar',
                          icon: Icons.school_rounded,
                          label: state.tr('faq_tab_student'),
                          onTap: () => setState(() {
                            _activeFaqTab = 'pelajar';
                            _openFaqIndex = null;
                          }),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _FaqTabCard(
                          isDark: isDark,
                          active: _activeFaqTab == 'orangtua',
                          icon: Icons.verified_user_rounded,
                          label: state.tr('faq_tab_parent'),
                          onTap: () => setState(() {
                            _activeFaqTab = 'orangtua';
                            _openFaqIndex = null;
                          }),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: _FaqTabCard(
                          isDark: isDark,
                          active: _activeFaqTab == 'komunitas',
                          icon: Icons.laptop_mac_rounded,
                          label: state.tr('faq_tab_community'),
                          onTap: () => setState(() {
                            _activeFaqTab = 'komunitas';
                            _openFaqIndex = null;
                          }),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),

                  // Accordion Daftar Pertanyaan (Tertutup saat belum diklik, smooth expand saat diklik)
                  Container(
                    decoration: BoxDecoration(
                      border: Border.symmetric(
                        horizontal: BorderSide(color: AppColors.border(isDark)),
                      ),
                    ),
                    child: Column(
                      children: List.generate((_faqGroups[_activeFaqTab] ?? []).length, (idx) {
                        final item = _faqGroups[_activeFaqTab]![idx];
                        final isOpen = _openFaqIndex == idx;

                        return Container(
                          decoration: BoxDecoration(
                            border: idx == 0 ? null : Border(top: BorderSide(color: AppColors.border(isDark))),
                          ),
                          child: InkWell(
                            onTap: () => setState(() => _openFaqIndex = isOpen ? null : idx),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 6),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          '${item['q'] ?? ''}',
                                          style: AppTheme.heading(
                                            isDark,
                                            size: 13.5,
                                            weight: isOpen ? FontWeight.w700 : FontWeight.w600,
                                            color: isOpen ? AppColors.sky : AppColors.textPrimary(isDark),
                                            height: 1.35,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      AnimatedRotation(
                                        turns: isOpen ? 0.125 : 0,
                                        duration: const Duration(milliseconds: 240),
                                        child: Container(
                                          width: 30,
                                          height: 30,
                                          decoration: BoxDecoration(
                                            shape: BoxShape.circle,
                                            color: isOpen ? AppColors.sky.withValues(alpha: 0.12) : Colors.transparent,
                                            border: Border.all(
                                              color: isOpen ? AppColors.sky.withValues(alpha: 0.4) : AppColors.border(isDark),
                                            ),
                                          ),
                                          child: Icon(
                                            Icons.add_rounded,
                                            size: 17,
                                            color: isOpen ? AppColors.sky : AppColors.textSecondary(isDark),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  AnimatedSize(
                                    duration: const Duration(milliseconds: 260),
                                    curve: Curves.easeOutCubic,
                                    child: isOpen
                                        ? Padding(
                                            padding: const EdgeInsets.only(top: 10, right: 24),
                                            child: Text(
                                              '${item['a'] ?? ''}',
                                              style: AppTheme.body(
                                                isDark,
                                                size: 12.5,
                                                color: AppColors.textSecondary(isDark),
                                                height: 1.6,
                                              ),
                                            ),
                                          )
                                        : const SizedBox.shrink(),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      }),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _FaqTabCard extends StatelessWidget {
  final bool isDark;
  final bool active;
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _FaqTabCard({
    required this.isDark,
    required this.active,
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
        decoration: BoxDecoration(
          color: active ? (isDark ? AppColors.sky.withValues(alpha: 0.18) : AppColors.skyTint) : AppColors.softBg(isDark),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: active ? AppColors.sky : AppColors.border(isDark),
            width: active ? 1.5 : 1,
          ),
        ),
        child: Column(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: active ? AppColors.sky : AppColors.cardBg(isDark),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 18, color: active ? Colors.white : AppColors.sky),
            ),
            const SizedBox(height: 6),
            Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: AppTheme.heading(isDark, size: 11.5, color: active ? AppColors.sky : AppColors.textPrimary(isDark)),
            ),
          ],
        ),
      ),
    );
  }
}

