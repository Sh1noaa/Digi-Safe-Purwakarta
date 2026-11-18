import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/minigame_art.dart';
import '../widgets/shared_widgets.dart';
import 'auth_screens.dart';
import 'minigame_play_screens.dart';
import 'profile_screens.dart';

class MinigameHubScreen extends StatefulWidget {
  final AppState appState;

  const MinigameHubScreen({super.key, required this.appState});

  @override
  State<MinigameHubScreen> createState() => _MinigameHubScreenState();
}

class _MinigameHubScreenState extends State<MinigameHubScreen> {
  String _search = '';
  bool _loading = true;
  int _lastVersion = -1;

  List<MinigameLevel> _levels = [];
  List<ScoreHistoryItem> _riwayat = [];
  List<LeaderboardEntry> _leaderboard = [];
  Map<String, dynamic> _stats = {
    'phishing_max': 0,
    'defender_max': 0,
    'link_max': 0,
    'total_main': 0,
    'akurasi': 0,
  };

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void didUpdateWidget(covariant MinigameHubScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.appState.contentVersion != _lastVersion) {
      _loadData();
    }
  }

  Future<void> _loadData() async {
    _lastVersion = widget.appState.contentVersion;
    setState(() => _loading = _levels.isEmpty);
    try {
      final res = await widget.appState.api.get('/minigame');
      if (!mounted) return;
      setState(() {
        _levels = (res['levels'] as List? ?? [])
            .map((e) => MinigameLevel.fromJson(e as Map<String, dynamic>))
            .toList();
        _riwayat = (res['riwayat'] as List? ?? [])
            .map((e) => ScoreHistoryItem.fromJson(e as Map<String, dynamic>))
            .toList();
        _leaderboard = (res['leaderboard'] as List? ?? [])
            .map((e) => LeaderboardEntry.fromJson(e as Map<String, dynamic>))
            .toList();
        if (res['stats'] is Map<String, dynamic>) {
          _stats = Map<String, dynamic>.from(res['stats'] as Map<String, dynamic>);
        }
      });
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _launchGame(String slug) {
    final state = widget.appState;
    if (!state.isAuthenticated) {
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => LoginScreen(appState: state)),
      );
      return;
    }

    Widget screen;
    if (slug == 'phishing') {
      screen = PhishingHunterScreen(appState: state);
    } else if (slug == 'cyber-defender') {
      screen = CyberDefenderScreen(appState: state);
    } else {
      screen = LinkTrapScreen(appState: state);
    }

    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen)).then((_) => _loadData());
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final isEn = state.isEn;
    final q = _search.trim().toLowerCase();

    final showPhishing = q.isEmpty || 'phishing hunter analisis pesan email chat'.contains(q);
    final showDefender = q.isEmpty || 'cyber defender pertahanan sistem virus malware'.contains(q);
    final showLinkTrap = q.isEmpty || 'link or trap inspeksi tautan url domain'.contains(q);

    return Scaffold(
      backgroundColor: isDark ? AppColors.darkBg : const Color(0xFFF8FAFC),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
          children: [
            // Search bar
            TextField(
              onChanged: (v) => setState(() => _search = v),
              decoration: InputDecoration(
                hintText: state.tr('game_search_hint'),
                hintStyle: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark)),
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                filled: true,
                fillColor: AppColors.cardBg(isDark),
                contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(99),
                  borderSide: BorderSide(color: AppColors.border(isDark)),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(99),
                  borderSide: BorderSide(color: AppColors.border(isDark)),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Retro monitor banner tanpa teks tambahan di atasnya (sesuai instruksi user!)
            ClipRRect(
              borderRadius: BorderRadius.circular(24),
              child: Image.asset(
                'assets/images/minigame-banner.jpg',
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),
            const SizedBox(height: 14),

            // 3 Kapsul Ringkasan
            Row(
              children: [
                Expanded(
                  child: _MiniCapCard(
                    isDark: isDark,
                    icon: Icons.mail_outline_rounded,
                    iconBg: AppColors.skySoft,
                    iconFg: AppColors.sky,
                    sub: state.tr('game_cap_1_sub'),
                    title: state.tr('game_cap_1_title'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _MiniCapCard(
                    isDark: isDark,
                    icon: Icons.shield_outlined,
                    iconBg: AppColors.sky,
                    iconFg: Colors.white,
                    sub: state.tr('game_cap_2_sub'),
                    title: state.tr('game_cap_2_title'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _MiniCapCard(
                    isDark: isDark,
                    icon: Icons.link_rounded,
                    iconBg: AppColors.lime,
                    iconFg: AppColors.ink,
                    sub: state.tr('game_cap_3_sub'),
                    title: state.tr('game_cap_3_title'),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            // Pilih Misi Permainan Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(state.tr('game_choose_mission'), style: AppTheme.heading(isDark, size: 19)),
                      Text(state.tr('game_choose_mission_sub'), style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark))),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(99)),
                  child: Text(
                    state.tr('game_badge', {'count': '${_levels.isEmpty ? 5 : _levels.length}'}),
                    style: AppTheme.heading(false, size: 11, color: AppColors.ink),
                  ),
                ),
              ],
            ),

            // Guest Notice jika belum login
            if (!state.isAuthenticated) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: isDark ? AppColors.sky.withValues(alpha: 0.14) : AppColors.skyTint,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: AppColors.sky.withValues(alpha: 0.3)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(color: const Color(0xFFFEF3C7), borderRadius: BorderRadius.circular(8)),
                          child: const Icon(Icons.lock_outline_rounded, size: 15, color: Color(0xFFB45309)),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            state.tr('game_guest_notice'),
                            style: AppTheme.body(isDark, size: 12, weight: FontWeight.w600),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        ElevatedButton(
                          onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => LoginScreen(appState: state))),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.ink,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                          ),
                          child: Text(state.tr('login'), style: AppTheme.heading(false, size: 11, color: Colors.white)),
                        ),
                        const SizedBox(width: 8),
                        ElevatedButton(
                          onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => RegisterScreen(appState: state))),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.lime,
                            foregroundColor: AppColors.ink,
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                          ),
                          child: Text(state.tr('register'), style: AppTheme.heading(false, size: 11, color: AppColors.ink)),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 28),

            // 3 KARTU MINIGAME (dengan ilustrasi UFO, Roket, Satelit)
            if (showPhishing)
              _GameMissionCard(
                slug: 'phishing',
                isDark: isDark,
                isAuthenticated: state.isAuthenticated,
                badge: state.tr('game_badge_1'),
                badgeBg: AppColors.skySoft,
                badgeFg: AppColors.sky,
                title: 'Phishing Hunter',
                desc: state.tr('game_desc_1'),
                actionLabel: state.isAuthenticated ? state.tr('game_play_now') : state.tr('game_guest_lock_1'),
                actionFg: AppColors.sky,
                onTap: () => _launchGame('phishing'),
              ),
            if (showDefender)
              _GameMissionCard(
                slug: 'cyber-defender',
                isDark: isDark,
                isAuthenticated: state.isAuthenticated,
                badge: state.tr('game_badge_2'),
                badgeBg: AppColors.skySoft,
                badgeFg: AppColors.sky,
                title: 'Cyber Defender',
                desc: state.tr('game_desc_2'),
                actionLabel: state.isAuthenticated ? state.tr('game_start_defense') : state.tr('game_guest_lock_2'),
                actionFg: AppColors.textPrimary(isDark),
                centered: true,
                onTap: () => _launchGame('cyber-defender'),
              ),
            if (showLinkTrap)
              _GameMissionCard(
                slug: 'link-trap',
                isDark: isDark,
                isAuthenticated: state.isAuthenticated,
                badge: state.tr('game_badge_3'),
                badgeBg: AppColors.lime.withValues(alpha: 0.55),
                badgeFg: AppColors.ink,
                title: 'Link or Trap?',
                desc: state.tr('game_desc_3'),
                actionLabel: state.isAuthenticated ? state.tr('game_test_url') : state.tr('game_guest_lock_3'),
                actionFg: AppColors.sky,
                onTap: () => _launchGame('link-trap'),
              ),

            const SizedBox(height: 16),

            // 5 LEVEL TANTANGAN KEAMANAN SIBER (di bawah pilihan game, sinkron langsung dari Admin!)
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.cardBg(isDark),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: AppColors.border(isDark)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(state.tr('game_level_system'), style: AppTheme.heading(isDark, size: 10, color: AppColors.sky)),
                  const SizedBox(height: 2),
                  Text(
                    state.tr('game_level_title', {'count': '${_levels.length}'}),
                    style: AppTheme.heading(isDark, size: 17),
                  ),
                  const SizedBox(height: 12),
                  if (_loading)
                    const Center(child: CircularProgressIndicator())
                  else
                    ..._levels.map((lvl) {
                      final badgeColors = AppColors.levelBadgeColors(lvl.warna, isDark);
                      return Container(
                        margin: const EdgeInsets.only(bottom: 10),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.softBg(isDark),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: AppColors.border(isDark)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: badgeColors[0],
                                    borderRadius: BorderRadius.circular(99),
                                  ),
                                  child: Text(lvl.badgeLabel, style: AppTheme.heading(false, size: 9.5, color: badgeColors[1])),
                                ),
                                Text(
                                  '⏱ ${lvl.waktuDetik}${state.tr('game_sec_short')}  •  +${lvl.poinBenar} pts',
                                  style: AppTheme.heading(isDark, size: 11, color: AppColors.emerald),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(lvl.namaLevel, style: AppTheme.heading(isDark, size: 14)),
                            const SizedBox(height: 2),
                            Text(
                              lvl.deskripsi,
                              style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark), height: 1.45),
                            ),
                          ],
                        ),
                      );
                    }),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // STATISTIK PERFORMA & LEADERBOARD
            if (state.isAuthenticated) ...[
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.cardBg(isDark),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(state.tr('game_stats_title'), style: AppTheme.heading(isDark, size: 16)),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(color: AppColors.skySoft, borderRadius: BorderRadius.circular(99)),
                          child: Text(
                            '${_stats['akurasi'] ?? 0}% ${isEn ? 'Accuracy' : 'Akurasi'}',
                            style: AppTheme.heading(false, size: 10.5, color: AppColors.sky),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: _MiniScoreBox(isDark: isDark, title: 'Phishing', val: '${_stats['phishing_max'] ?? 0}')),
                        const SizedBox(width: 8),
                        Expanded(child: _MiniScoreBox(isDark: isDark, title: 'Defender', val: '${_stats['defender_max'] ?? 0}')),
                        const SizedBox(width: 8),
                        Expanded(child: _MiniScoreBox(isDark: isDark, title: 'Link Trap', val: '${_stats['link_max'] ?? 0}')),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Riwayat Bermain
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.cardBg(isDark),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(state.tr('game_history_title'), style: AppTheme.heading(isDark, size: 16)),
                    Text(state.tr('game_history_sub'), style: AppTheme.body(isDark, size: 11.5, color: AppColors.textSecondary(isDark))),
                    const SizedBox(height: 12),
                    if (_riwayat.isEmpty)
                      Text(isEn ? 'No matches played yet.' : 'Belum ada riwayat permainan.', style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)))
                    else
                      ..._riwayat.map((h) => ListTile(
                            contentPadding: EdgeInsets.zero,
                            leading: CircleAvatar(
                              backgroundColor: AppColors.skySoft,
                              child: Text(h.gameName[0], style: AppTheme.heading(false, size: 13, color: AppColors.sky)),
                            ),
                            title: Text(h.gameName, style: AppTheme.heading(isDark, size: 13)),
                            subtitle: Text(h.playedAt, style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark))),
                            trailing: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(color: AppColors.lime, borderRadius: BorderRadius.circular(99)),
                              child: Text('${h.skor} pts', style: AppTheme.heading(false, size: 11, color: AppColors.ink)),
                            ),
                          )),
                  ],
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Leaderboard Top Warga
            if (_leaderboard.isNotEmpty)
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.cardBg(isDark),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(state.tr('game_leaderboard_title'), style: AppTheme.heading(isDark, size: 16)),
                    const SizedBox(height: 12),
                    ...List.generate(_leaderboard.length, (i) {
                      final entry = _leaderboard[i];
                      return InkWell(
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => ProfileScreen(appState: state, userId: entry.id)),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          child: Row(
                            children: [
                              Container(
                                width: 26,
                                height: 26,
                                alignment: Alignment.center,
                                decoration: BoxDecoration(
                                  color: i == 0 ? AppColors.lime : AppColors.softBg(isDark),
                                  shape: BoxShape.circle,
                                ),
                                child: Text('#${i + 1}', style: AppTheme.heading(false, size: 10.5, color: i == 0 ? AppColors.ink : AppColors.textPrimary(isDark))),
                              ),
                              const SizedBox(width: 10),
                              UserAvatarWidget(
                                avatarUrl: entry.avatarUrl,
                                initial: entry.inisial,
                                nameSeed: entry.namaLengkap,
                                size: 36,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(entry.namaLengkap, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 13)),
                                    Text('${entry.matchesPlayed}x • @${entry.username}', style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
                                  ],
                                ),
                              ),
                              Text('${entry.topScore} pts', style: AppTheme.heading(isDark, size: 13, color: AppColors.sky)),
                            ],
                          ),
                        ),
                      );
                    }),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _MiniCapCard extends StatelessWidget {
  final bool isDark;
  final IconData icon;
  final Color iconBg;
  final Color iconFg;
  final String sub;
  final String title;

  const _MiniCapCard({
    required this.isDark,
    required this.icon,
    required this.iconBg,
    required this.iconFg,
    required this.sub,
    required this.title,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: AppColors.cardBg(isDark),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border(isDark)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(color: iconBg, borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, size: 16, color: iconFg),
          ),
          const SizedBox(height: 8),
          Text(sub, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.body(isDark, size: 9.5, color: AppColors.textSecondary(isDark))),
          Text(title, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 11)),
        ],
      ),
    );
  }
}

class _GameMissionCard extends StatelessWidget {
  final String slug;
  final bool isDark;
  final bool isAuthenticated;
  final String badge;
  final Color badgeBg;
  final Color badgeFg;
  final String title;
  final String desc;
  final String actionLabel;
  final Color actionFg;
  final bool centered;
  final VoidCallback onTap;

  const _GameMissionCard({
    required this.slug,
    required this.isDark,
    required this.isAuthenticated,
    required this.badge,
    required this.badgeBg,
    required this.badgeFg,
    required this.title,
    required this.desc,
    required this.actionLabel,
    required this.actionFg,
    this.centered = false,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 28),
        decoration: BoxDecoration(
          color: AppColors.cardBg(isDark),
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: AppColors.border(isDark)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 16,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: centered ? CrossAxisAlignment.center : CrossAxisAlignment.start,
          children: [
            MinigameHeaderArt(slug: slug, isDark: isDark),
            Padding(
              padding: const EdgeInsets.fromLTRB(18, 10, 18, 16),
              child: Column(
                crossAxisAlignment: centered ? CrossAxisAlignment.center : CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                    decoration: BoxDecoration(color: badgeBg, borderRadius: BorderRadius.circular(99)),
                    child: Text(badge, style: AppTheme.heading(false, size: 10, color: badgeFg)),
                  ),
                  const SizedBox(height: 8),
                  Text(title, style: AppTheme.heading(isDark, size: 19)),
                  const SizedBox(height: 4),
                  Text(
                    desc,
                    textAlign: centered ? TextAlign.center : TextAlign.left,
                    style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.5),
                  ),
                ],
              ),
            ),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 15, horizontal: 16),
              decoration: BoxDecoration(
                color: isAuthenticated ? AppColors.cardBg(isDark) : AppColors.softBg(isDark),
                border: Border(top: BorderSide(color: AppColors.border(isDark))),
                borderRadius: const BorderRadius.vertical(bottom: Radius.circular(24)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  if (!isAuthenticated) ...[
                    const Icon(Icons.lock_outline_rounded, size: 15, color: AppColors.amber),
                    const SizedBox(width: 6),
                  ],
                  Flexible(
                    child: Text(
                      actionLabel,
                      overflow: TextOverflow.ellipsis,
                      style: AppTheme.heading(
                        isDark,
                        size: 12,
                        color: isAuthenticated ? actionFg : AppColors.textPrimary(isDark),
                      ),
                    ),
                  ),
                  if (isAuthenticated) ...[
                    const SizedBox(width: 6),
                    Icon(Icons.play_arrow_rounded, size: 18, color: actionFg),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MiniScoreBox extends StatelessWidget {
  final bool isDark;
  final String title;
  final String val;

  const _MiniScoreBox({required this.isDark, required this.title, required this.val});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.softBg(isDark),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border(isDark)),
      ),
      child: Column(
        children: [
          Text(title, style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
          const SizedBox(height: 4),
          Text(val, style: AppTheme.heading(isDark, size: 18, color: AppColors.sky)),
        ],
      ),
    );
  }
}

