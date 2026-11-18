import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';
import 'auth_screens.dart';
import 'leaderboard_screen.dart';
import 'password_checker_screen.dart';
import 'profile_screens.dart';
import 'tentang_screen.dart';
import 'testimoni_screen.dart';

class BerandaScreen extends StatefulWidget {
  final AppState appState;

  const BerandaScreen({super.key, required this.appState});

  @override
  State<BerandaScreen> createState() => _BerandaScreenState();
}

class _BerandaScreenState extends State<BerandaScreen> with SingleTickerProviderStateMixin {
  bool _loading = true;
  String? _error;
  int _lastLoadedVersion = -1;

  Map<String, dynamic> _aboutStats = {};
  List<Map<String, dynamic>> _services = [];
  Map<String, List<Map<String, dynamic>>> _faqGroups = {};
  List<UserProfile> _registeredUsers = [];

  String _activeFaqTab = 'pelajar';
  int? _openFaqIndex;

  // 3D Hero Cards Carousel Controller
  final PageController _card3dCtrl = PageController(viewportFraction: 0.68, initialPage: 1);
  double _card3dPage = 1.0;

  // 60fps Draggable + Clickable Marquee Ticker
  final ScrollController _marqueeCtrl = ScrollController();
  late final Ticker _marqueeTicker;
  bool _userTouchingMarquee = false;
  DateTime _resumeMarqueeAfter = DateTime.now();

  @override
  void initState() {
    super.initState();
    _card3dCtrl.addListener(() {
      if (mounted) {
        setState(() => _card3dPage = _card3dCtrl.page ?? 1.0);
      }
    });

    if (widget.appState.warmBerandaJson != null) {
      _applyBerandaPayload(widget.appState.warmBerandaJson!);
      _loading = false;
    }

    _marqueeTicker = createTicker((_) {
      if (!mounted || !_marqueeCtrl.hasClients || _userTouchingMarquee) return;
      if (DateTime.now().isBefore(_resumeMarqueeAfter)) return;
      final maxScroll = _marqueeCtrl.position.maxScrollExtent;
      if (maxScroll <= 0) return;
      final half = maxScroll / 2.0;
      double next = _marqueeCtrl.offset + 0.65;
      if (next >= half) {
        next -= half;
      }
      _marqueeCtrl.jumpTo(next);
    })
      ..start();

    _loadData();
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
    _marqueeTicker.dispose();
    _marqueeCtrl.dispose();
    _card3dCtrl.dispose();
    super.dispose();
  }

  void _applyBerandaPayload(Map<String, dynamic> res) {
    _aboutStats = Map<String, dynamic>.from(res['about_stats'] ?? {});
    _services = (res['services'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();

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
        _applyBerandaPayload(res);
      });
    } catch (e) {
      if (mounted && _registeredUsers.isEmpty) setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;

    final hero3dCards = [
      (title: 'Phishing Shield', metric: '99.2%', tag: isEn ? 'URL Guard' : 'Filter Tautan', icon: Icons.verified_user_rounded, bg: const [Color(0xFF0EA5E9), Color(0xFF1D4ED8)]),
      (title: 'Bot Digika', metric: '24/7', tag: isEn ? 'Smart Alert' : 'Notif Otomatis', icon: Icons.smart_toy_rounded, bg: const [Color(0xFF2D46E6), Color(0xFF4F46E5)]),
      (title: 'Quiz Arena', metric: '5 Level', tag: isEn ? 'Live Sync' : 'Soal Admin', icon: Icons.sports_esports_rounded, bg: const [Color(0xFF10B981), Color(0xFF047857)]),
    ];

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: const EdgeInsets.only(bottom: 112),
        children: [
          // 1. HERO LANGIT BIRU + KAWANAN BURUNG + KARTU 3D MELENGKUNG (Website Parity!)
          Padding(
            padding: const EdgeInsets.fromLTRB(10, 10, 10, 0),
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(26),
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
                            const Color(0xFF105EB2).withValues(alpha: 0.90),
                            const Color(0xFF1E88E5).withValues(alpha: 0.56),
                            const Color(0xFF2196F3).withValues(alpha: 0.22),
                            const Color(0xFF105EB2).withValues(alpha: 0.45),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const Positioned.fill(child: FlyingBirdsOverlay()),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(18, 28, 18, 26),
                    child: Column(
                      children: [
                        if (state.isAuthenticated) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.96),
                              borderRadius: BorderRadius.circular(99),
                            ),
                            child: Wrap(
                              crossAxisAlignment: WrapCrossAlignment.center,
                              alignment: WrapAlignment.center,
                              spacing: 6,
                              children: [
                                Text(
                                  '${state.tr('logged_in_as')} ',
                                  style: AppTheme.body(false, size: 11, color: AppColors.muted),
                                ),
                                Text(
                                  state.user!.namaLengkap,
                                  overflow: TextOverflow.ellipsis,
                                  style: AppTheme.heading(false, size: 12, color: AppColors.ink),
                                ),
                                RoleBadgeWidget(
                                  isOwner: state.user!.isOwner,
                                  isAdmin: state.user!.isAdmin,
                                  compact: true,
                                ),
                                SpecialtyRoleChip(
                                  specialtyRole: state.user!.specialtyRole,
                                  isDark: false,
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
                          style: AppTheme.heading(false, size: 26, color: Colors.white, height: 1.14),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          state.tr('hero_subtitle'),
                          textAlign: TextAlign.center,
                          style: AppTheme.body(false, size: 13, color: Colors.white.withValues(alpha: 0.92), height: 1.55),
                        ),
                        const SizedBox(height: 20),

                        // CTA Pills
                        Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          alignment: WrapAlignment.center,
                          children: [
                            GestureDetector(
                              onTap: () => state.setTab(1),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 17, vertical: 12),
                                decoration: BoxDecoration(
                                  color: AppColors.ink.withValues(alpha: 0.32),
                                  borderRadius: BorderRadius.circular(99),
                                  border: Border.all(color: Colors.white.withValues(alpha: 0.36)),
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
                                  state.setTab(2);
                                } else {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => AuthWelcomeScreen(
                                        appState: state,
                                        openRegisterInitially: true,
                                        popOnSuccess: true,
                                      ),
                                    ),
                                  );
                                }
                              },
                              child: Container(
                                padding: const EdgeInsets.fromLTRB(18, 7, 7, 7),
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
                                    const SizedBox(width: 9),
                                    Container(
                                      width: 28,
                                      height: 28,
                                      decoration: const BoxDecoration(color: AppColors.ink, shape: BoxShape.circle),
                                      child: const Icon(Icons.north_east_rounded, size: 14, color: Colors.white),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 22),

                        // Interactive 3D Curved Perspective Cards (mirroring Hero 3D Arc on website!)
                        SizedBox(
                          height: 122,
                          child: PageView.builder(
                            controller: _card3dCtrl,
                            itemCount: hero3dCards.length,
                            itemBuilder: (context, idx) {
                              final c = hero3dCards[idx];
                              final diff = (_card3dPage - idx).clamp(-1.5, 1.5);
                              final rotateY = diff * 0.42;
                              final scale = 1.0 - (diff.abs() * 0.12);
                              return Transform(
                                alignment: Alignment.center,
                                transform: Matrix4.identity()
                                  ..setEntry(3, 2, 0.0014)
                                  ..rotateY(rotateY)
                                  ..scaleByDouble(scale, scale, 1.0, 1.0),
                                child: GestureDetector(
                                  onTap: () => state.setTab(idx == 2 ? 2 : 1),
                                  child: Container(
                                    margin: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      gradient: LinearGradient(colors: c.bg, begin: Alignment.topLeft, end: Alignment.bottomRight),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: Colors.white.withValues(alpha: 0.45)),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.black.withValues(alpha: 0.22),
                                          blurRadius: 14,
                                          offset: const Offset(0, 6),
                                        ),
                                      ],
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Icon(c.icon, color: AppColors.lime, size: 22),
                                            const SizedBox(width: 6),
                                            Flexible(
                                              child: Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                decoration: BoxDecoration(
                                                  color: Colors.white.withValues(alpha: 0.2),
                                                  borderRadius: BorderRadius.circular(99),
                                                ),
                                                child: Text(c.tag, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w700)),
                                              ),
                                            ),
                                          ],
                                        ),
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(c.metric, style: AppTheme.heading(false, size: 20, color: Colors.white)),
                                            Text(c.title, style: AppTheme.body(false, size: 11.5, weight: FontWeight.w600, color: Colors.white.withValues(alpha: 0.9))),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              );
                            },
                          ),
                        ),

                        const SizedBox(height: 16),
                        Text(
                          state.tr('trusted_users'),
                          style: AppTheme.body(false, size: 11.5, weight: FontWeight.w500, color: Colors.white.withValues(alpha: 0.9)),
                        ),
                        const SizedBox(height: 3),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(5, (_) => const Icon(Icons.star_rounded, color: AppColors.lime, size: 17)),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 1B. SCANNER KEBIASAAN AMAN 88% (Website Section 2 Parity + Compact Responsive!)
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
            child: _HabitScanner88Card(isDark: isDark, isEn: isEn),
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
              padding: const EdgeInsets.fromLTRB(16, 30, 16, 0),
              child: Column(
                children: [
                  DigiSectionKicker(text: state.tr('about_badge'), isDark: isDark),
                  const SizedBox(height: 8),
                  Text(
                    state.tr('about_title_1'),
                    textAlign: TextAlign.center,
                    style: AppTheme.heading(isDark, size: 21, height: 1.22),
                  ),
                  const SizedBox(height: 18),

                  // Sky Card: 120+ Mitra
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
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
                            Text('DIGI•SAFE', style: AppTheme.heading(false, size: 15, color: Colors.white)),
                            Container(
                              width: 32,
                              height: 32,
                              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
                              child: const Icon(Icons.bar_chart_rounded, color: AppColors.ink, size: 17),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(15),
                          decoration: BoxDecoration(
                            color: AppColors.cardBg(isDark),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('${_aboutStats['mitra_count'] ?? '120+'}', style: AppTheme.heading(isDark, size: 26)),
                              const SizedBox(height: 4),
                              Text('${_aboutStats['mitra_desc'] ?? ''}', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.5)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: AppColors.softBg(isDark),
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: AppColors.border(isDark)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('${_aboutStats['komitmen_label'] ?? ''}', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark))),
                        const SizedBox(height: 4),
                        Text('${_aboutStats['komitmen_persen'] ?? '100%'}', style: AppTheme.heading(isDark, size: 30)),
                        const SizedBox(height: 10),
                        Text('${_aboutStats['komitmen_quote'] ?? ''}', style: AppTheme.body(isDark, size: 12.5, weight: FontWeight.w500, height: 1.55)),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  Row(
                    children: [
                      Expanded(
                        flex: 3,
                        child: Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(20)),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('${_aboutStats['modul_label'] ?? ''}', style: AppTheme.body(false, size: 11, weight: FontWeight.w600, color: AppColors.ink.withValues(alpha: 0.75))),
                              const SizedBox(height: 4),
                              Text('${_aboutStats['modul_interaksi'] ?? '520k+'}', style: AppTheme.heading(false, size: 24, color: AppColors.ink)),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        flex: 2,
                        child: Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(color: AppColors.ink, borderRadius: BorderRadius.circular(20)),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('${_aboutStats['kecamatan_count'] ?? '20+'}', style: AppTheme.heading(false, size: 22, color: Colors.white)),
                              const SizedBox(height: 2),
                              Text('${_aboutStats['kecamatan_label'] ?? ''}', maxLines: 2, overflow: TextOverflow.ellipsis, style: AppTheme.body(false, size: 10.5, color: Colors.white.withValues(alpha: 0.75))),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // 3. LAYANAN KAMI (Bergaya Folder Grid + Navigasi Instan)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 36, 16, 0),
              child: Column(
                children: [
                  DigiSectionKicker(text: state.tr('services_badge'), isDark: isDark),
                  const SizedBox(height: 8),
                  Text(state.tr('services_title'), textAlign: TextAlign.center, style: AppTheme.heading(isDark, size: 21, height: 1.22)),
                  const SizedBox(height: 8),
                  Text(state.tr('services_desc'), textAlign: TextAlign.center, style: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark), height: 1.5)),
                  const SizedBox(height: 18),

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
                            padding: const EdgeInsets.all(16),
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
                                  decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(12)),
                                  child: Icon(iconData, color: AppColors.ink, size: 22),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text('${srv['title'] ?? ''}', style: AppTheme.heading(isDark, size: 15.5)),
                                      const SizedBox(height: 4),
                                      Text('${srv['description'] ?? ''}', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.5)),
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

            // 3B. PINTASAN FITUR WEBSITE LENGKAP (Leaderboard, Password Lab, Testimoni, Tentang)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 22, 16, 0),
              child: GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                childAspectRatio: 1.75,
                children: [
                  _QuickFeatureTile(
                    isDark: isDark,
                    icon: Icons.leaderboard_rounded,
                    color: AppColors.cobalt,
                    title: 'Leaderboard',
                    subtitle: isEn ? '4 Categories' : '4 Kategori Top',
                    onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => LeaderboardScreen(appState: state))),
                  ),
                  _QuickFeatureTile(
                    isDark: isDark,
                    icon: Icons.password_rounded,
                    color: AppColors.emerald,
                    title: isEn ? 'Password Lab' : 'Cek Password',
                    subtitle: isEn ? 'Strength Tester' : 'Uji Kekuatan Sandi',
                    onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => PasswordCheckerScreen(appState: state))),
                  ),
                  _QuickFeatureTile(
                    isDark: isDark,
                    icon: Icons.star_rounded,
                    color: AppColors.amber,
                    title: state.tr('nav_testimoni'),
                    subtitle: isEn ? 'Citizen Stories' : 'Suara Komunitas',
                    onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => TestimoniScreen(appState: state))),
                  ),
                  _QuickFeatureTile(
                    isDark: isDark,
                    icon: Icons.info_rounded,
                    color: AppColors.sky,
                    title: state.tr('nav_tentang'),
                    subtitle: isEn ? 'Creator & Vision' : 'Profil Pengembang',
                    onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => TentangScreen(appState: state))),
                  ),
                ],
              ),
            ),

            // 4. DIREKTORI WARGA SIBER TERDAFTAR (DRAGGABLE KANAN-KIRI + NONSTOP 60FPS + TETAP KLIK!)
            if (_registeredUsers.isNotEmpty) ...[
              Container(
                margin: const EdgeInsets.only(top: 32),
                padding: const EdgeInsets.symmetric(vertical: 26),
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
                          Text(state.tr('users_title'), style: AppTheme.heading(isDark, size: 20)),
                          const SizedBox(height: 4),
                          Text(
                            state.tr('users_desc', {'count': '${_registeredUsers.length}'}),
                            style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                          ),
                          const SizedBox(height: 12),
                          GestureDetector(
                            onTap: () => Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => UsersDirectoryScreen(appState: state)),
                            ),
                            child: Container(
                              padding: const EdgeInsets.fromLTRB(16, 7, 7, 7),
                              decoration: BoxDecoration(color: AppColors.ink, borderRadius: BorderRadius.circular(99)),
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
                    const SizedBox(height: 16),
                    SizedBox(
                      height: 158,
                      child: Listener(
                        onPointerDown: (_) {
                          _userTouchingMarquee = true;
                        },
                        onPointerUp: (_) {
                          _userTouchingMarquee = false;
                          _resumeMarqueeAfter = DateTime.now().add(const Duration(milliseconds: 650));
                        },
                        onPointerCancel: (_) {
                          _userTouchingMarquee = false;
                        },
                        child: ListView.builder(
                          controller: _marqueeCtrl,
                          physics: const BouncingScrollPhysics(),
                          scrollDirection: Axis.horizontal,
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: _registeredUsers.length * 2,
                          itemBuilder: (context, idx) {
                            final u = _registeredUsers[idx % _registeredUsers.length];
                            return GestureDetector(
                              onTap: () => Navigator.of(context).push(
                                MaterialPageRoute(builder: (_) => ProfileScreen(appState: state, userId: u.id)),
                              ),
                              child: Container(
                                width: 252,
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
                                              Text(u.namaLengkap, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 13)),
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
                                    if (!u.isPrivateForVisitor && u.specialtyRole != null)
                                      SpecialtyRoleChip(specialtyRole: u.specialtyRole, isDark: isDark, compact: true)
                                    else
                                      const SizedBox.shrink(),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          u.isPrivateForVisitor ? '🔒 Privat' : '${u.stats.totalSkor ?? 0} pts',
                                          style: AppTheme.heading(isDark, size: 11.5, color: AppColors.sky),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(color: AppColors.softBg(isDark), borderRadius: BorderRadius.circular(99)),
                                          child: Text(isEn ? 'View Profile →' : 'Lihat Profil →', style: AppTheme.body(isDark, size: 10.5, weight: FontWeight.w600)),
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
                    ),
                  ],
                ),
              ),
            ],

            // 5. Q & A INTERAKTIF
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 36, 16, 26),
              child: Column(
                children: [
                  RichText(
                    textAlign: TextAlign.center,
                    text: TextSpan(
                      style: AppTheme.heading(isDark, size: 25),
                      children: [
                        TextSpan(text: '${state.tr('faq_heading_1')} '),
                        TextSpan(text: state.tr('faq_heading_2'), style: const TextStyle(color: AppColors.sky)),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(state.tr('faq_subtitle'), textAlign: TextAlign.center, style: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark), height: 1.5)),
                  const SizedBox(height: 18),

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

                  Container(
                    decoration: BoxDecoration(
                      border: Border.symmetric(horizontal: BorderSide(color: AppColors.border(isDark))),
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
                                          child: Icon(Icons.add_rounded, size: 17, color: isOpen ? AppColors.sky : AppColors.textSecondary(isDark)),
                                        ),
                                      ),
                                    ],
                                  ),
                                  AnimatedSize(
                                    duration: const Duration(milliseconds: 260),
                                    curve: Curves.easeOutCubic,
                                    child: isOpen
                                        ? Padding(
                                            padding: const EdgeInsets.only(top: 10, right: 20),
                                            child: Text(
                                              '${item['a'] ?? ''}',
                                              style: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark), height: 1.6),
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

            // 6. FOOTER WEBSITE MIRROR
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 14),
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.ink,
                borderRadius: BorderRadius.circular(24),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 34,
                        height: 34,
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
                        child: Image.asset('assets/images/logo.png'),
                      ),
                      const SizedBox(width: 10),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Digi-Safe Purwakarta', style: AppTheme.heading(false, size: 15, color: Colors.white)),
                          Text('SMK Negeri 2 Purwakarta • Pure Native APK', style: AppTheme.body(false, size: 10.5, color: Colors.white70)),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    isEn
                        ? 'Synced live with digisafepurwakarta.web.id database.'
                        : 'Sinkron real-time dengan database resmi website digisafepurwakarta.web.id.',
                    style: AppTheme.body(false, size: 11.5, color: Colors.white60),
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

class _HabitScanner88Card extends StatelessWidget {
  final bool isDark;
  final bool isEn;

  const _HabitScanner88Card({required this.isDark, required this.isEn});

  @override
  Widget build(BuildContext context) {
    final badHabits = isEn
        ? ['Random APK install', 'Share OTP codes', 'Reuse 1 password']
        : ['Asal klik file APK', 'Bagikan kode OTP', '1 sandi semua akun'];
    final goodHabits = isEn
        ? ['Verify official URL', 'Activate 2FA shield', 'Strong unique pass']
        : ['Cek domain resmi', 'Aktifkan fitur 2FA', 'Password unik & kuat'];

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppColors.border(isDark)),
        boxShadow: [
          BoxShadow(
            color: AppColors.sky.withValues(alpha: isDark ? 0.08 : 0.10),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Flexible(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.sky.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(99),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.radar_rounded, size: 13, color: AppColors.sky),
                      const SizedBox(width: 4),
                      Flexible(
                        child: Text(
                          isEn ? 'HABIT SCANNER' : 'SCANNER KEBIASAAN',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: AppTheme.heading(isDark, size: 9.5, color: AppColors.sky),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Flexible(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(99)),
                  child: Text(
                    isEn ? '88% Safer Habits' : '88% Kebiasaan Aman',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: AppTheme.heading(false, size: 10, color: AppColors.ink),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: badHabits
                      .map((t) => Container(
                            margin: const EdgeInsets.only(bottom: 6),
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.danger.withValues(alpha: isDark ? 0.14 : 0.08),
                              borderRadius: BorderRadius.circular(99),
                            ),
                            child: Text(
                              '✕ $t',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: AppTheme.body(isDark, size: 10.5, weight: FontWeight.w500, color: isDark ? const Color(0xFFFCA5A5).withValues(alpha: 0.78) : const Color(0xFFB91C1C).withValues(alpha: 0.75)),
                            ),
                          ))
                      .toList(),
                ),
              ),
              Container(
                width: 3,
                height: 82,
                margin: const EdgeInsets.symmetric(horizontal: 10),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [Colors.transparent, AppColors.sky, AppColors.lime, Colors.transparent],
                  ),
                  borderRadius: BorderRadius.circular(99),
                  boxShadow: [
                    BoxShadow(color: AppColors.sky.withValues(alpha: 0.55), blurRadius: 8),
                  ],
                ),
              ),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: goodHabits
                      .map((t) => Container(
                            margin: const EdgeInsets.only(bottom: 6),
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.emerald.withValues(alpha: isDark ? 0.18 : 0.10),
                              borderRadius: BorderRadius.circular(99),
                              border: Border.all(color: AppColors.emerald.withValues(alpha: 0.28)),
                            ),
                            child: Text(
                              '✓ $t',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: AppTheme.body(isDark, size: 10.5, weight: FontWeight.w700, color: isDark ? const Color(0xFF6EE7B7) : const Color(0xFF047857)),
                            ),
                          ))
                      .toList(),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _QuickFeatureTile extends StatelessWidget {
  final bool isDark;
  final IconData icon;
  final Color color;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _QuickFeatureTile({
    required this.isDark,
    required this.icon,
    required this.color,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.cardBg(isDark),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppColors.border(isDark)),
        ),
        child: Row(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: color.withValues(alpha: isDark ? 0.22 : 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 13)),
                  Text(subtitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
                ],
              ),
            ),
          ],
        ),
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
          border: Border.all(color: active ? AppColors.sky : AppColors.border(isDark), width: active ? 1.5 : 1),
        ),
        child: Column(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(color: active ? AppColors.sky : AppColors.cardBg(isDark), borderRadius: BorderRadius.circular(10)),
              child: Icon(icon, size: 18, color: active ? Colors.white : AppColors.sky),
            ),
            const SizedBox(height: 6),
            Text(label, maxLines: 1, overflow: TextOverflow.ellipsis, textAlign: TextAlign.center, style: AppTheme.heading(isDark, size: 11.5, color: active ? AppColors.sky : AppColors.textPrimary(isDark))),
          ],
        ),
      ),
    );
  }
}
