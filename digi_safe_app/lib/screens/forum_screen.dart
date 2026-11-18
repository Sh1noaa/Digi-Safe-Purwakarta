import 'dart:async';

import 'package:flutter/material.dart';

import '../core/app_state.dart';
import '../core/theme.dart';
import '../models/digisafe_models.dart';
import '../widgets/shared_widgets.dart';
import 'auth_screens.dart';
import 'profile_screens.dart';

class ForumScreen extends StatefulWidget {
  final AppState appState;

  const ForumScreen({super.key, required this.appState});

  @override
  State<ForumScreen> createState() => _ForumScreenState();
}

class _ForumScreenState extends State<ForumScreen> {
  List<ForumThread> _forums = [];
  bool _loading = true;
  int _lastVersion = -1;

  String _sort = 'terbaru'; // terbaru | membantu
  String _category = 'semua';
  String _search = '';
  int? _openThreadId;

  Timer? _pollTimer;

  static const _categories = [
    ('semua', AppColors.ink),
    ('umum', AppColors.sky),
    ('phishing', Color(0xFFEA580C)),
    ('malware', Color(0xFF7C3AED)),
    ('privasi', Color(0xFF059669)),
    ('tanya-pakar', Color(0xFF0284C7)),
  ];

  @override
  void initState() {
    super.initState();
    _loadForums();
    _pollTimer = Timer.periodic(const Duration(seconds: 7), (_) {
      if (mounted && widget.appState.currentTabIndex == 3) {
        _loadForums(silent: true);
      }
    });
  }

  @override
  void didUpdateWidget(covariant ForumScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.appState.contentVersion != _lastVersion) {
      _loadForums();
    }
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadForums({bool silent = false}) async {
    _lastVersion = widget.appState.contentVersion;
    if (!silent) {
      setState(() => _loading = _forums.isEmpty);
    }
    try {
      final res = await widget.appState.api.get('/forum?sort=$_sort');
      final raw = res['forums'];
      if (raw is List && mounted) {
        setState(() {
          _forums = raw.map((e) => ForumThread.fromJson(e as Map<String, dynamic>)).toList();
        });
      }
    } catch (_) {
    } finally {
      if (mounted && !silent) setState(() => _loading = false);
    }
  }

  List<ForumThread> get _filtered {
    return _forums.where((f) {
      final catMatch = _category == 'semua' || f.kategori == _category;
      final q = _search.trim().toLowerCase();
      final textMatch = q.isEmpty ||
          f.judul.toLowerCase().contains(q) ||
          f.pertanyaan.toLowerCase().contains(q) ||
          f.authorName.toLowerCase().contains(q);
      return catMatch && textMatch;
    }).toList();
  }

  String _catLabel(String slug) => widget.appState.tr('forum_cat_$slug');

  void _openCreateModal() {
    if (!widget.appState.isAuthenticated) {
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => LoginScreen(appState: widget.appState)));
      return;
    }

    final titleCtrl = TextEditingController();
    final bodyCtrl = TextEditingController();
    String selectedCat = 'umum';
    bool submitting = false;
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
                Text(widget.appState.tr('forum_create_btn'), style: AppTheme.heading(isDark, size: 18)),
                const SizedBox(height: 12),
                TextField(
                  controller: titleCtrl,
                  decoration: InputDecoration(
                    labelText: widget.appState.isEn ? 'Topic Title' : 'Judul Diskusi',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 10),
                DropdownButtonFormField<String>(
                  initialValue: selectedCat,
                  decoration: InputDecoration(
                    labelText: widget.appState.isEn ? 'Category' : 'Kategori',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  items: ['umum', 'phishing', 'malware', 'privasi', 'tanya-pakar']
                      .map((c) => DropdownMenuItem(value: c, child: Text(_catLabel(c))))
                      .toList(),
                  onChanged: (v) => setModalState(() => selectedCat = v ?? 'umum'),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: bodyCtrl,
                  maxLines: 4,
                  decoration: InputDecoration(
                    labelText: widget.appState.isEn ? 'Question Details' : 'Uraikan Pertanyaan / Kasus',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: submitting
                        ? null
                        : () async {
                            if (titleCtrl.text.trim().isEmpty || bodyCtrl.text.trim().isEmpty) return;
                            setModalState(() => submitting = true);
                            try {
                              await widget.appState.api.post('/forum', {
                                'judul': titleCtrl.text.trim(),
                                'kategori': selectedCat,
                                'pertanyaan': bodyCtrl.text.trim(),
                              });
                              if (ctx.mounted) Navigator.of(ctx).pop();
                              await _loadForums();
                            } catch (e) {
                              if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
                            } finally {
                              if (ctx.mounted) setModalState(() => submitting = false);
                            }
                          },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.sky,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                    ),
                    child: submitting
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : Text(widget.appState.tr('forum_create_btn'), style: AppTheme.heading(false, size: 13, color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _openThreadModal(ForumThread thread) {
    _openThreadId = thread.id;
    final replyCtrl = TextEditingController();
    final isDark = widget.appState.isDark;
    bool sending = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          final current = _forums.firstWhere((f) => f.id == _openThreadId, orElse: () => thread);
          return Container(
            constraints: BoxConstraints(maxHeight: MediaQuery.of(ctx).size.height * 0.90),
            padding: EdgeInsets.fromLTRB(18, 18, 18, MediaQuery.of(ctx).viewInsets.bottom + 18),
            decoration: BoxDecoration(
              color: AppColors.cardBg(isDark),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${_catLabel(current.kategori)} • ${current.authorName} • ${current.waktu}',
                            style: AppTheme.body(isDark, size: 11, color: AppColors.sky, weight: FontWeight.w600),
                          ),
                          const SizedBox(height: 4),
                          Text(current.judul, style: AppTheme.heading(isDark, size: 17)),
                        ],
                      ),
                    ),
                    IconButton(onPressed: () => Navigator.of(ctx).pop(), icon: const Icon(Icons.close_rounded)),
                  ],
                ),
                const SizedBox(height: 10),
                Expanded(
                  child: ListView(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? AppColors.sky.withValues(alpha: 0.12) : AppColors.skyTint,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.sky.withValues(alpha: 0.2)),
                        ),
                        child: Text(current.pertanyaan, style: AppTheme.body(isDark, size: 13, height: 1.55)),
                      ),
                      const SizedBox(height: 14),
                      Text(
                        '${widget.appState.tr('forum_replies_list')} (${current.jawaban.length})',
                        style: AppTheme.heading(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                      ),
                      const SizedBox(height: 10),
                      ...current.jawaban.map((ans) {
                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: ans.isMostHelpful
                                ? (isDark ? AppColors.emerald.withValues(alpha: 0.12) : const Color(0xFFECFDF5))
                                : AppColors.softBg(isDark),
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(
                              color: ans.isMostHelpful ? AppColors.emerald : AppColors.border(isDark),
                              width: ans.isMostHelpful ? 1.5 : 1,
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  UserAvatarWidget(
                                    avatarUrl: ans.userAvatar,
                                    initial: ans.userInitial,
                                    nameSeed: ans.userName,
                                    isOwner: ans.isOwner,
                                    size: 30,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Wrap(
                                      spacing: 6,
                                      crossAxisAlignment: WrapCrossAlignment.center,
                                      children: [
                                        Text(ans.userName, style: AppTheme.heading(isDark, size: 12)),
                                        RoleBadgeWidget(isOwner: ans.isOwner, isAdmin: ans.isAdmin, compact: true),
                                        if (ans.isMostHelpful)
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                            decoration: BoxDecoration(color: AppColors.emerald, borderRadius: BorderRadius.circular(99)),
                                            child: Text('👍 Most Helpful', style: AppTheme.heading(false, size: 9, color: Colors.white)),
                                          ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text(ans.jawaban, style: AppTheme.body(isDark, size: 12.5, height: 1.5)),
                              const SizedBox(height: 10),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(ans.waktu, style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
                                  Row(
                                    children: [
                                      _VotePillButton(
                                        isDark: isDark,
                                        emoji: '👍',
                                        count: ans.upCount,
                                        active: ans.userVote == 'up',
                                        activeColor: AppColors.emerald,
                                        onTap: () async {
                                          if (!widget.appState.isAuthenticated) {
                                            Navigator.of(ctx).pop();
                                            Navigator.of(context).push(MaterialPageRoute(builder: (_) => LoginScreen(appState: widget.appState)));
                                            return;
                                          }
                                          try {
                                            final res = await widget.appState.api.post('/forum/jawaban/${ans.id}/vote', {'vote_type': 'up'});
                                            if (res['forum'] is Map<String, dynamic>) {
                                              final updated = ForumThread.fromJson(res['forum'] as Map<String, dynamic>);
                                              setState(() {
                                                _forums = _forums.map((f) => f.id == updated.id ? updated : f).toList();
                                              });
                                              setModalState(() {});
                                            }
                                          } catch (_) {}
                                        },
                                      ),
                                      const SizedBox(width: 6),
                                      _VotePillButton(
                                        isDark: isDark,
                                        emoji: '👎',
                                        count: ans.downCount,
                                        active: ans.userVote == 'down',
                                        activeColor: AppColors.danger,
                                        onTap: () async {
                                          if (!widget.appState.isAuthenticated) {
                                            Navigator.of(ctx).pop();
                                            Navigator.of(context).push(MaterialPageRoute(builder: (_) => LoginScreen(appState: widget.appState)));
                                            return;
                                          }
                                          try {
                                            final res = await widget.appState.api.post('/forum/jawaban/${ans.id}/vote', {'vote_type': 'down'});
                                            if (res['forum'] is Map<String, dynamic>) {
                                              final updated = ForumThread.fromJson(res['forum'] as Map<String, dynamic>);
                                              setState(() {
                                                _forums = _forums.map((f) => f.id == updated.id ? updated : f).toList();
                                              });
                                              setModalState(() {});
                                            }
                                          } catch (_) {}
                                        },
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      }),
                    ],
                  ),
                ),
                const Divider(),
                if (widget.appState.isAuthenticated)
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: replyCtrl,
                          decoration: InputDecoration(
                            hintText: widget.appState.tr('forum_write_reply_hint'),
                            filled: true,
                            fillColor: AppColors.softBg(isDark),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton(
                        onPressed: sending
                            ? null
                            : () async {
                                if (replyCtrl.text.trim().isEmpty) return;
                                setModalState(() => sending = true);
                                try {
                                  final res = await widget.appState.api.post('/forum/${current.id}/jawaban', {
                                    'jawaban': replyCtrl.text.trim(),
                                  });
                                  replyCtrl.clear();
                                  if (res['forum'] is Map<String, dynamic>) {
                                    final updated = ForumThread.fromJson(res['forum'] as Map<String, dynamic>);
                                    setState(() {
                                      _forums = _forums.map((f) => f.id == updated.id ? updated : f).toList();
                                    });
                                    setModalState(() {});
                                  }
                                } finally {
                                  if (ctx.mounted) setModalState(() => sending = false);
                                }
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.sky,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                        ),
                        child: Text(sending ? '...' : 'Kirim'),
                      ),
                    ],
                  )
                else
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.of(ctx).pop();
                        Navigator.of(context).push(MaterialPageRoute(builder: (_) => LoginScreen(appState: widget.appState)));
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.lime,
                        foregroundColor: AppColors.ink,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                      ),
                      child: Text(widget.appState.tr('forum_login_ask'), style: AppTheme.heading(false, size: 12, color: AppColors.ink)),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    ).whenComplete(() => _openThreadId = null);
  }

  @override
  Widget build(BuildContext context) {
    final state = widget.appState;
    final isDark = state.isDark;
    final items = _filtered;

    return Scaffold(
      backgroundColor: isDark ? AppColors.darkBg : AppColors.skyTint,
      body: RefreshIndicator(
        onRefresh: () => _loadForums(),
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            // Header Bersih Responsive dengan Teks Biru (sesuai instruksi responsive web!)
            Container(
              color: AppColors.scaffoldBg(isDark),
              padding: const EdgeInsets.fromLTRB(16, 22, 16, 20),
              child: Column(
                children: [
                  RichText(
                    textAlign: TextAlign.center,
                    text: TextSpan(
                      style: AppTheme.heading(isDark, size: 27, height: 1.15),
                      children: [
                        TextSpan(text: '${state.tr('forum_hero_1')} '),
                        TextSpan(text: '${state.tr('forum_hero_2')}\n', style: const TextStyle(color: AppColors.sky)),
                        TextSpan(text: state.tr('forum_hero_3')),
                      ],
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    state.tr('forum_hero_sub'),
                    textAlign: TextAlign.center,
                    style: AppTheme.body(isDark, size: 12.5, color: AppColors.textSecondary(isDark)),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      ElevatedButton(
                        onPressed: _openCreateModal,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.sky,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: Text(
                          state.isAuthenticated ? state.tr('forum_create_btn') : state.tr('forum_login_ask'),
                          style: AppTheme.heading(false, size: 12, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  Text(
                    state.tr('forum_hub_title'),
                    textAlign: TextAlign.center,
                    style: AppTheme.heading(isDark, size: 19, color: AppColors.sky),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    state.tr('forum_hub_sub'),
                    textAlign: TextAlign.center,
                    style: AppTheme.body(isDark, size: 11.5, color: AppColors.sky.withValues(alpha: 0.85)),
                  ),
                  const SizedBox(height: 12),

                  // Category Dock Pills
                  Wrap(
                    spacing: 7,
                    runSpacing: 7,
                    alignment: WrapAlignment.center,
                    children: _categories.map((cat) {
                      final active = _category == cat.$1;
                      return GestureDetector(
                        onTap: () => setState(() => _category = cat.$1),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 180),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: cat.$2,
                            borderRadius: BorderRadius.circular(14),
                            border: active ? Border.all(color: AppColors.lime, width: 2.2) : null,
                          ),
                          child: Text(
                            _catLabel(cat.$1),
                            style: AppTheme.heading(false, size: 11, color: Colors.white),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ],
              ),
            ),

            // Container Daftar Diskusi
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 14, 12, 36),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.cardBg(isDark),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.border(isDark)),
                ),
                child: Column(
                  children: [
                    Row(
                      children: [
                        _SortPill(
                          isDark: isDark,
                          active: _sort == 'terbaru',
                          label: state.tr('forum_sort_newest'),
                          activeBg: AppColors.ink,
                          onTap: () {
                            setState(() => _sort = 'terbaru');
                            _loadForums();
                          },
                        ),
                        const SizedBox(width: 8),
                        _SortPill(
                          isDark: isDark,
                          active: _sort == 'membantu',
                          label: state.tr('forum_sort_helpful'),
                          activeBg: AppColors.sky,
                          onTap: () {
                            setState(() => _sort = 'membantu');
                            _loadForums();
                          },
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(99),
                            border: Border.all(color: const Color(0xFFA7F3D0)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.circle, size: 7, color: AppColors.emerald),
                              const SizedBox(width: 4),
                              Text('Live', style: AppTheme.heading(false, size: 10, color: const Color(0xFF047857))),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      onChanged: (v) => setState(() => _search = v),
                      decoration: InputDecoration(
                        hintText: state.tr('forum_search_hint'),
                        hintStyle: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark)),
                        prefixIcon: const Icon(Icons.search_rounded, size: 18),
                        filled: true,
                        fillColor: AppColors.softBg(isDark),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(99), borderSide: BorderSide.none),
                      ),
                    ),
                    const SizedBox(height: 14),

                    if (_loading)
                      const Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator())
                    else if (items.isEmpty)
                      Padding(
                        padding: const EdgeInsets.all(28),
                        child: Text(
                          state.isEn ? 'No discussions match this filter.' : 'Belum ada diskusi pada kategori ini.',
                          style: AppTheme.body(isDark, color: AppColors.textSecondary(isDark)),
                        ),
                      )
                    else
                      ...items.map((item) => Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: item.isStarred ? (isDark ? AppColors.darkSurface : const Color(0xFFFAFDFF)) : AppColors.softBg(isDark),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: item.isStarred ? AppColors.sky : AppColors.border(isDark),
                                width: item.isStarred ? 1.5 : 1,
                              ),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (item.isStarred || item.hasMostHelpful)
                                  Padding(
                                    padding: const EdgeInsets.only(bottom: 10),
                                    child: Wrap(
                                      spacing: 6,
                                      runSpacing: 6,
                                      children: [
                                        if (item.isStarred)
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                            decoration: BoxDecoration(color: AppColors.ink, borderRadius: BorderRadius.circular(99)),
                                            child: Text('★ ${state.tr('forum_pinned')}', style: AppTheme.heading(false, size: 9.5, color: AppColors.lime)),
                                          ),
                                        if (item.hasMostHelpful)
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                            decoration: BoxDecoration(color: const Color(0xFFD1FAE5), borderRadius: BorderRadius.circular(99)),
                                            child: Text('👍 ${state.tr('forum_most_helpful')}', style: AppTheme.heading(false, size: 9.5, color: const Color(0xFF065F46))),
                                          ),
                                      ],
                                    ),
                                  ),

                                Row(
                                  children: [
                                    GestureDetector(
                                      onTap: item.userId == null
                                          ? null
                                          : () => Navigator.of(context).push(
                                                MaterialPageRoute(builder: (_) => ProfileScreen(appState: state, userId: item.userId)),
                                              ),
                                      child: UserAvatarWidget(
                                        avatarUrl: item.authorAvatar,
                                        initial: item.authorInitial,
                                        nameSeed: item.authorName,
                                        isOwner: item.authorIsOwner,
                                        size: 36,
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
                                                child: Text(item.authorName, overflow: TextOverflow.ellipsis, style: AppTheme.heading(isDark, size: 12.5)),
                                              ),
                                              const SizedBox(width: 6),
                                              RoleBadgeWidget(isOwner: item.authorIsOwner, isAdmin: item.authorIsAdmin, compact: true),
                                            ],
                                          ),
                                          Text(item.waktu, style: AppTheme.body(isDark, size: 10.5, color: AppColors.textSecondary(isDark))),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: AppColors.skySoft.withValues(alpha: isDark ? 0.2 : 1),
                                        borderRadius: BorderRadius.circular(99),
                                      ),
                                      child: Text(_catLabel(item.kategori), style: AppTheme.heading(isDark, size: 9.5, color: AppColors.sky)),
                                    ),
                                  ],
                                ),

                                const SizedBox(height: 12),
                                GestureDetector(
                                  onTap: () => _openThreadModal(item),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(item.judul, style: AppTheme.heading(isDark, size: 15.5, height: 1.3)),
                                      const SizedBox(height: 6),
                                      Text(
                                        item.pertanyaan,
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                        style: AppTheme.body(isDark, size: 12, color: AppColors.textSecondary(isDark), height: 1.5),
                                      ),
                                    ],
                                  ),
                                ),

                                const SizedBox(height: 12),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Row(
                                      children: [
                                        Text(
                                          '💬 ${item.jawabanCount} ${state.tr('forum_answers_count')}',
                                          style: AppTheme.body(isDark, size: 11, weight: FontWeight.w600, color: AppColors.textSecondary(isDark)),
                                        ),
                                        const SizedBox(width: 8),
                                        Text('👍 ${item.totalUp}', style: AppTheme.heading(isDark, size: 11, color: AppColors.emerald)),
                                        const SizedBox(width: 6),
                                        Text('👎 ${item.totalDown}', style: AppTheme.heading(isDark, size: 11, color: AppColors.danger)),
                                      ],
                                    ),
                                    ElevatedButton.icon(
                                      onPressed: () => _openThreadModal(item),
                                      icon: const Icon(Icons.chat_bubble_outline_rounded, size: 14),
                                      label: Text(state.tr('forum_opinion_btn'), style: AppTheme.heading(isDark, size: 11, color: AppColors.sky)),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: isDark ? AppColors.sky.withValues(alpha: 0.16) : AppColors.skyTint,
                                        foregroundColor: AppColors.sky,
                                        elevation: 0,
                                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          )),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SortPill extends StatelessWidget {
  final bool isDark;
  final bool active;
  final String label;
  final Color activeBg;
  final VoidCallback onTap;

  const _SortPill({
    required this.isDark,
    required this.active,
    required this.label,
    required this.activeBg,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: active ? activeBg : AppColors.softBg(isDark),
          borderRadius: BorderRadius.circular(99),
        ),
        child: Text(
          label,
          style: AppTheme.heading(isDark, size: 11.5, color: active ? Colors.white : AppColors.textSecondary(isDark)),
        ),
      ),
    );
  }
}

class _VotePillButton extends StatelessWidget {
  final bool isDark;
  final String emoji;
  final int count;
  final bool active;
  final Color activeColor;
  final VoidCallback onTap;

  const _VotePillButton({
    required this.isDark,
    required this.emoji,
    required this.count,
    required this.active,
    required this.activeColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: active ? activeColor.withValues(alpha: 0.18) : AppColors.cardBg(isDark),
          borderRadius: BorderRadius.circular(99),
          border: Border.all(color: active ? activeColor : AppColors.border(isDark)),
        ),
        child: Text('$emoji $count', style: AppTheme.heading(isDark, size: 11, color: active ? activeColor : AppColors.textPrimary(isDark))),
      ),
    );
  }
}

