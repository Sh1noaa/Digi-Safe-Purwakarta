import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';
import 'profile_screens.dart';

class TestimoniScreen extends StatefulWidget {
  final AppState appState;

  const TestimoniScreen({super.key, required this.appState});

  @override
  State<TestimoniScreen> createState() => _TestimoniScreenState();
}

class _TestimoniScreenState extends State<TestimoniScreen> {
  List<TestimoniItem> _items = [];
  bool _loading = true;
  int _lastVersion = -1;
  int _activeIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void didUpdateWidget(covariant TestimoniScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.appState.contentVersion != _lastVersion) {
      _loadData();
    }
  }

  Future<void> _loadData() async {
    _lastVersion = widget.appState.contentVersion;
    setState(() => _loading = _items.isEmpty);
    try {
      final res = await widget.appState.api.get('/testimoni');
      final raw = res['testimonis'];
      if (raw is List && mounted) {
        setState(() {
          _items = raw.map((e) => TestimoniItem.fromJson(e as Map<String, dynamic>)).toList();
          if (_activeIndex >= _items.length) _activeIndex = 0;
        });
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _openWriteModal() {
    final storyCtrl = TextEditingController();
    int rating = 5;
    bool isAnon = false;
    bool sending = false;
    final isDark = widget.appState.isDark;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Container(
          padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(ctx).viewInsets.bottom + 24),
          decoration: BoxDecoration(
            color: AppColors.cardBg(isDark),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(widget.appState.tr('testi_write_title'), style: AppTheme.heading(isDark, size: 18)),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(5, (i) {
                    final star = i + 1;
                    return IconButton(
                      onPressed: () => setModalState(() => rating = star),
                      icon: Icon(
                        Icons.star_rounded,
                        size: 34,
                        color: star <= rating ? const Color(0xFFF59E0B) : AppColors.border(isDark),
                      ),
                    );
                  }),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: storyCtrl,
                  maxLines: 4,
                  decoration: InputDecoration(
                    hintText: widget.appState.tr('testi_story_hint'),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
                  ),
                ),
                const SizedBox(height: 8),
                SwitchListTile(
                  value: isAnon,
                  onChanged: (v) => setModalState(() => isAnon = v),
                  contentPadding: EdgeInsets.zero,
                  title: Text(widget.appState.tr('testi_anon_toggle'), style: AppTheme.body(isDark, size: 12.5)),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: sending
                        ? null
                        : () async {
                            if (storyCtrl.text.trim().isEmpty) return;
                            setModalState(() => sending = true);
                            try {
                              await widget.appState.api.post('/testimoni', {
                                'rating': rating,
                                'cerita': storyCtrl.text.trim(),
                                'is_anonymous': isAnon,
                              });
                              if (ctx.mounted) Navigator.of(ctx).pop();
                              await _loadData();
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text(widget.appState.isEn ? 'Review published!' : 'Ulasan berhasil diterbitkan!')),
                                );
                              }
                            } catch (e) {
                              if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
                            } finally {
                              if (ctx.mounted) setModalState(() => sending = false);
                            }
                          },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.ink,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                    ),
                    child: sending
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.lime))
                        : Text(widget.appState.tr('testi_submit'), style: AppTheme.heading(false, size: 13, color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          // Hero Minimalist Identik Testimoni Web
          Container(
            color: AppColors.scaffoldBg(isDark),
            padding: const EdgeInsets.fromLTRB(20, 32, 20, 32),
            child: Column(
              children: [
                Container(
                  width: 62,
                  height: 62,
                  decoration: BoxDecoration(
                    color: AppColors.sky,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(color: AppColors.sky.withValues(alpha: 0.25), blurRadius: 16, offset: const Offset(0, 6)),
                    ],
                  ),
                  child: const Icon(Icons.verified_user_rounded, color: Colors.white, size: 30),
                ),
                const SizedBox(height: 18),
                Text(
                  state.tr('testi_title'),
                  textAlign: TextAlign.center,
                  style: AppTheme.heading(isDark, size: 28, height: 1.15),
                ),
                const SizedBox(height: 10),
                Text(
                  state.tr('testi_sub'),
                  textAlign: TextAlign.center,
                  style: AppTheme.body(isDark, size: 13, color: AppColors.textSecondary(isDark), height: 1.5),
                ),
                const SizedBox(height: 22),

                // CTA Tulis Ulasan Pill
                GestureDetector(
                  onTap: _openWriteModal,
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(24, 10, 10, 10),
                    decoration: BoxDecoration(
                      color: AppColors.ink,
                      borderRadius: BorderRadius.circular(99),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.14), blurRadius: 18, offset: const Offset(0, 8)),
                      ],
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              state.tr('testi_cta_kicker'),
                              style: AppTheme.heading(false, size: 9.5, color: AppColors.lime),
                            ),
                            Text(
                              state.tr('testi_cta_btn'),
                              style: AppTheme.heading(false, size: 16, color: Colors.white),
                            ),
                          ],
                        ),
                        const SizedBox(width: 18),
                        Container(
                          width: 44,
                          height: 44,
                          decoration: const BoxDecoration(color: AppColors.lime, shape: BoxShape.circle),
                          child: const Icon(Icons.edit_rounded, color: AppColors.ink, size: 20),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Daftar Ulasan Komunitas
          Container(
            color: isDark ? AppColors.darkSurface : const Color(0xFFF5F6F9),
            padding: const EdgeInsets.fromLTRB(16, 28, 16, 40),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                DigiSectionKicker(text: state.tr('testi_section_kicker'), isDark: isDark),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(state.tr('testi_section_title'), style: AppTheme.heading(isDark, size: 22)),
                    if (_items.isNotEmpty)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(color: AppColors.skySoft, borderRadius: BorderRadius.circular(99)),
                        child: Text('${_items.length}', style: AppTheme.heading(false, size: 12, color: AppColors.sky)),
                      ),
                  ],
                ),
                const SizedBox(height: 16),

                if (_loading)
                  const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
                else
                  ..._items.map((t) => Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: AppColors.cardBg(isDark),
                          borderRadius: BorderRadius.circular(22),
                          border: Border.all(color: AppColors.border(isDark)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                GestureDetector(
                                  onTap: (!t.isAnonymous && t.userId != null)
                                      ? () => Navigator.of(context).push(
                                            MaterialPageRoute(builder: (_) => ProfileScreen(appState: state, userId: t.userId)),
                                          )
                                      : null,
                                  child: UserAvatarWidget(
                                    avatarUrl: t.avatar,
                                    initial: t.inisial,
                                    nameSeed: t.nama,
                                    isOwner: t.isOwner,
                                    size: 40,
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          Flexible(
                                            child: Text(t.nama, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 13.5)),
                                          ),
                                          const SizedBox(width: 6),
                                          RoleBadgeWidget(isOwner: t.isOwner, isAdmin: false, compact: true),
                                        ],
                                      ),
                                      Text(t.waktu, style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark))),
                                    ],
                                  ),
                                ),
                                Row(
                                  children: List.generate(
                                    5,
                                    (s) => Icon(
                                      Icons.star_rounded,
                                      size: 16,
                                      color: (s + 1) <= t.rating ? const Color(0xFFF59E0B) : AppColors.border(isDark),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            Text('“${t.cerita}”', style: AppTheme.body(isDark, size: 13, height: 1.55)),
                          ],
                        ),
                      )),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

