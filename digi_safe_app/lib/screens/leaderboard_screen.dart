import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';
import 'profile_screens.dart';

class LeaderboardScreen extends StatefulWidget {
  final AppState appState;

  const LeaderboardScreen({super.key, required this.appState});

  @override
  State<LeaderboardScreen> createState() => _LeaderboardScreenState();
}

class _LeaderboardScreenState extends State<LeaderboardScreen> {
  List<LeaderboardCategoryModel> _categories = [];
  int _selectedIdx = 0;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final res = await widget.appState.api.getOptional('/leaderboard');
      if (res != null && res['categories'] is List) {
        final cats = (res['categories'] as List)
            .map((e) => LeaderboardCategoryModel.fromJson(e as Map<String, dynamic>))
            .toList();
        if (mounted) {
          setState(() {
            _categories = cats;
            if (_selectedIdx >= cats.length) _selectedIdx = 0;
          });
        }
        return;
      }

      // Fallback ke /minigame leaderboard apabila endpoint /api/leaderboard baru belum di-upload
      final mini = await widget.appState.api.get('/minigame');
      final entries = (mini['leaderboard'] as List? ?? [])
          .map((e) => LeaderboardEntry.fromJson(e as Map<String, dynamic>))
          .toList();
      final fallbackRows = List.generate(entries.length, (i) {
        final e = entries[i];
        return LeaderboardRowModel(
          id: e.id,
          rank: i + 1,
          nama: e.namaLengkap,
          username: e.username,
          inisial: e.inisial,
          avatarUrl: e.avatarUrl,
          bio: 'Warga Siber Purwakarta',
          metricMain: '${e.topScore} pts',
          metricMobile: '${e.topScore} pts',
          subtitle: '${e.matchesPlayed}x bermain • @${e.username}',
          stars: 5,
          accuracy: 100,
        );
      });
      if (mounted) {
        setState(() {
          _categories = [
            LeaderboardCategoryModel(
              key: 'minigame_benar',
              label: widget.appState.isEn ? 'Top Minigame Players' : 'Skor Tertinggi Minigame',
              shortLabel: widget.appState.isEn ? 'Top Scores' : 'Skor Top',
              rows: fallbackRows,
            ),
          ];
        });
      }
    } catch (e) {
      if (mounted) setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.appState.isDark;
    final isEn = widget.appState.isEn;
    final activeCat = _categories.isNotEmpty ? _categories[_selectedIdx] : null;
    final rows = activeCat?.rows ?? [];

    return Scaffold(
      backgroundColor: AppColors.softBg(isDark),
      appBar: AppBar(
        title: Text(widget.appState.tr('leaderboard_title'), style: AppTheme.heading(isDark, size: 16)),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null && _categories.isEmpty
                ? Center(child: Text(_error!, style: AppTheme.body(isDark, color: AppColors.danger)))
                : ListView(
                    padding: const EdgeInsets.fromLTRB(16, 12, 16, 36),
                    children: [
                      // Hero Rotary Category Card
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: isDark
                                ? [const Color(0xFF0F172A), const Color(0xFF1E3A8A)]
                                : [const Color(0xFF1D4ED8), AppColors.sky],
                          ),
                          borderRadius: BorderRadius.circular(26),
                        ),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Container(
                                  width: 44,
                                  height: 44,
                                  decoration: BoxDecoration(
                                    color: AppColors.lime,
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  child: const Icon(Icons.emoji_events_rounded, color: AppColors.ink, size: 24),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'PURWAKARTA CYBER ARENA',
                                        style: AppTheme.heading(false, size: 10, color: AppColors.lime),
                                      ),
                                      Text(
                                        activeCat?.label ?? widget.appState.tr('leaderboard_title'),
                                        style: AppTheme.heading(false, size: 18, color: Colors.white),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            if (_categories.length > 1) ...[
                              const SizedBox(height: 16),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: List.generate(_categories.length, (i) {
                                  final selected = i == _selectedIdx;
                                  return GestureDetector(
                                    onTap: () => setState(() => _selectedIdx = i),
                                    child: AnimatedContainer(
                                      duration: const Duration(milliseconds: 220),
                                      padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
                                      decoration: BoxDecoration(
                                        color: selected ? AppColors.lime : Colors.white.withValues(alpha: 0.16),
                                        borderRadius: BorderRadius.circular(99),
                                      ),
                                      child: Text(
                                        _categories[i].shortLabel,
                                        style: AppTheme.heading(
                                          false,
                                          size: 11.5,
                                          color: selected ? AppColors.ink : Colors.white,
                                        ),
                                      ),
                                    ),
                                  );
                                }),
                              ),
                            ],
                          ],
                        ),
                      ),

                      const SizedBox(height: 18),

                      if (rows.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(28),
                          decoration: BoxDecoration(
                            color: AppColors.cardBg(isDark),
                            borderRadius: BorderRadius.circular(22),
                            border: Border.all(color: AppColors.border(isDark)),
                          ),
                          child: Text(
                            isEn ? 'No ranking data in this category yet.' : 'Belum ada data peringkat di kategori ini.',
                            textAlign: TextAlign.center,
                            style: AppTheme.body(isDark, color: AppColors.textSecondary(isDark)),
                          ),
                        )
                      else ...[
                        // Top 3 Podium Strip
                        if (rows.length >= 2)
                          Container(
                            margin: const EdgeInsets.only(bottom: 16),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: AppColors.cardBg(isDark),
                              borderRadius: BorderRadius.circular(24),
                              border: Border.all(color: AppColors.border(isDark)),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                if (rows.length >= 2) Expanded(child: _PodiumTile(row: rows[1], height: 76, isDark: isDark, state: widget.appState)),
                                const SizedBox(width: 8),
                                Expanded(child: _PodiumTile(row: rows[0], height: 102, isDark: isDark, state: widget.appState, crown: true)),
                                const SizedBox(width: 8),
                                if (rows.length >= 3)
                                  Expanded(child: _PodiumTile(row: rows[2], height: 64, isDark: isDark, state: widget.appState))
                                else
                                  const Spacer(),
                              ],
                            ),
                          ),

                        // Full List
                        ...List.generate(rows.length, (i) {
                          final r = rows[i];
                          return GestureDetector(
                            onTap: () => Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => ProfileScreen(appState: widget.appState, userId: r.id)),
                            ),
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: AppColors.cardBg(isDark),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: i == 0 ? AppColors.lime : AppColors.border(isDark),
                                  width: i == 0 ? 1.5 : 1,
                                ),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 30,
                                    height: 30,
                                    alignment: Alignment.center,
                                    decoration: BoxDecoration(
                                      color: i == 0 ? AppColors.lime : AppColors.softBg(isDark),
                                      shape: BoxShape.circle,
                                    ),
                                    child: Text(
                                      '#${r.rank}',
                                      style: AppTheme.heading(false, size: 11, color: i == 0 ? AppColors.ink : AppColors.textPrimary(isDark)),
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  UserAvatarWidget(
                                    avatarUrl: r.avatarUrl,
                                    initial: r.inisial,
                                    nameSeed: r.nama,
                                    size: 42,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(r.nama, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 13.5)),
                                        Text(r.subtitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark))),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                    decoration: BoxDecoration(
                                      color: AppColors.sky.withValues(alpha: 0.12),
                                      borderRadius: BorderRadius.circular(99),
                                    ),
                                    child: Text(r.metricMain, style: AppTheme.heading(isDark, size: 11, color: AppColors.sky)),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }),
                      ],
                    ],
                  ),
      ),
    );
  }
}

class _PodiumTile extends StatelessWidget {
  final LeaderboardRowModel row;
  final double height;
  final bool isDark;
  final AppState state;
  final bool crown;

  const _PodiumTile({
    required this.row,
    required this.height,
    required this.isDark,
    required this.state,
    this.crown = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => ProfileScreen(appState: state, userId: row.id)),
      ),
      child: Column(
        children: [
          if (crown) const Icon(Icons.workspace_premium_rounded, color: AppColors.amber, size: 22),
          UserAvatarWidget(
            avatarUrl: row.avatarUrl,
            initial: row.inisial,
            nameSeed: row.nama,
            size: crown ? 52 : 42,
            ringColor: crown ? AppColors.lime : AppColors.sky,
          ),
          const SizedBox(height: 6),
          Text(
            row.nama.split(' ').first,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: AppTheme.heading(isDark, size: 12),
          ),
          const SizedBox(height: 6),
          Container(
            height: math.max(54, height),
            width: double.infinity,
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: crown ? AppColors.lime : AppColors.softBg(isDark),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: crown ? AppColors.lime : AppColors.border(isDark)),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text('#${row.rank}', style: AppTheme.heading(false, size: 16, color: crown ? AppColors.ink : AppColors.sky)),
                Text(
                  row.metricMobile,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: AppTheme.body(false, size: 10.5, weight: FontWeight.w700, color: crown ? AppColors.ink : AppColors.textPrimary(isDark)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
